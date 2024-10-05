/* GCPL-GT Update CMD column in Users Table
ST alerts - 662
ST alertactions - 630
ST alertassignees - 1380
ST alertschedules - 1329

alerts - 880
alertactions - 892
alertassignees - 22327
alertschedules - 1530

Cron - 3 5 * * *
alert query - {"query": "SELECT '{{userID}}' as userID"}
assignees tmvariable - {"userID":1}
alertactiontype id - 7
Ticket - https://projects.zoho.in/portal/mobisy#taskdetail/15779000002340099/15779000003899297/15779000008660694
*/

echo "<pre>";  

$lastuserID = $datum[0]["userID"];
$currentDateTime = date('Y-m-d H:i:s');

$database = "g-next_bizom_in_bizom";
$db = $this->companyConnection('companyMasterDatasource', $database);

$userQuery = "SELECT DISTINCT users.id, users.employeeid, users.cmd 
              FROM users 
              WHERE users.id > $lastuserID
              AND users.active = 1 
              AND (employeeid <> '' OR employeeid IS NOT NULL) 
              ORDER BY users.id;";
$userData = $db->execute($userQuery)->fetchAll('assoc');

$processedData = [];
$updateCases = [];
$userIds = [];

foreach ($userData as $user) {
    $userId = $user['id'];
    $emp_id = $user['employeeid'];
    $current_cmd = $user['cmd']; 
    $cmd = isset(explode('_', $emp_id)[1]) ? explode('_', $emp_id)[1] : '';

    $processedData[] = [
        "userId" => $userId,
        "employeeId" => $emp_id,
        "current_cmd" => $current_cmd,
        "cmd" => $cmd,
        "executedOn" => $currentDateTime
    ];

    $updateCases[] = "WHEN id = $userId THEN '$cmd'";
    $userIds[] = $userId;

    $_UPDATEVAR["userID"] = $userId;
}

if (!empty($updateCases) && !empty($userIds)) {
    $updateQuery = "UPDATE users 
                    SET cmd = CASE " . implode(' ', $updateCases) . " END, 
                        modified = '$currentDateTime' 
                    WHERE id IN (" . implode(',', $userIds) . ")";
    $db->execute($updateQuery);
}

$filename = "g_next_cmd_update.csv";
$filePath = "/tmp/" . $filename;
$file = fopen($filePath, "w");

fputcsv($file, ["User ID", "Employee ID", "Current CMD", "Updated CMD", "ExecutedOn"]);

foreach ($processedData as $row) {
    fputcsv($file, $row);
}

fclose($file);

$config = [];
$config["to"] = ["alerts@mobisy.com"];
$config["cc"] = ["manojkumar.hriday@mobisy.com"];
$config["subject"] = "GCPL-GT User CMD Column Update";
$config["message"] = "Please find the attached file.";
$config["attachments"] = [$filePath];
$this->runAction('email', $config);

unlink($filePath);

$this->dropCompanyConnection($db->configKeyName);

