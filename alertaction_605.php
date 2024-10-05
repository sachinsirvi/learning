/**
[Samsung TL] attendance data need to be inserted - 338
ST Alert ID : 374  {"query": "SELECT '{{lastFormID}}' as lastFormID"}
ST Alert Action ID : 338
ST Alert Assignee ID : 947 {"lastFormID":"1499604"}
ST Alert Schedule ID : 912  5 21 * * *
Alert Ticket : https://projects.zoho.in/portal/mobisy#taskdetail/15779000002340099/15779000002384047/15779000005798578
  
Alert ID : 588  {"query": "SELECT '{{lastFormID}}' as lastFormID"} //1497554
Alert Action ID : 605
Alert Assignee ID : 21977 {"lastFormID":"1526629"} 
Alert Schedule ID : 1183 5 21 * * *
*/

use Cake\Core\Configure;

echo "<pre>";

$database = Configure::read("_environment") == 'Staging' ? "stagingsamsungtl_bizomstaging_in_bizom" : "samsungtl_bizom_in_bizom";
$db = $this->companyConnection("companyMasterDatasource", $database);

$lastFormId = $datum[0]["lastFormID"];

$toDate = date("Y-m-25");
$toDay = date("Y-m-d");
$firstDayOfCycle = date("Y-m-26", strtotime("-1 month", strtotime($toDate)));

$dateTime = date("Y-m-d H:i:s");
$last30Days = date("Y-m-d", strtotime("-30 days", strtotime($toDay)));
$last7Days = date("Y-m-d", strtotime("-7 days", strtotime($toDay)));

$formQuery = "
    SELECT 
        genericformdatas.id AS form_id, 
        genericformdatas.user_id AS user_id, 
        DATE(users.created) AS created_date,
        JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"Reasons#DropDown\"'), '$[0][0]')) AS reason,
        JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"status#RadioButton\"'), '$[0][0]')) AS status,
        JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"Present Type#RadioButton\"'), '$[0][0]')) AS present_type,
        JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"Leave Type#RadioButton\"'), '$[0][0]')) AS leave_type,
        JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"Attendance Date #SingleLineText\"'), '$[0][0]')) AS attendance_date,    
        SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"Present Type#RadioButton\"'), '$[0][0]')), '-', -1) AS present_type_id,
        SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"Leave Type#RadioButton\"'), '$[0][0]')), '-', -1) AS leave_type_id
               
    FROM genericformdatas
    LEFT JOIN users ON users.id = genericformdatas.user_id
    WHERE genericformdatas.id > $lastFormId
    AND JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(genericformdatas.genericdata, '$**.\"Attendance Date #SingleLineText\"'), '$[0][0]')) != '' 
    ORDER BY genericformdatas.id
";

$formData = $_DB->execute($formQuery)->fetchAll("assoc");

$logQueries = "Total records fetched: " . count($formData) . "\n\n";

$processedUsers = [];

foreach ($formData as $fData) {
    $formId = $fData["form_id"];
    $userId = $fData["user_id"];
    $status = $fData["status"];
    $presentTypeId = $fData["present_type_id"];
    $leaveTypeId = $fData["leave_type_id"];
    $attendanceDate = $fData["attendance_date"];
    $userCreatedDate = $fData["created_date"];
    $logTime = '00:00:00';

    if (!empty($attendanceDate)) {
        // Check if attendance date is greater than or equal to the user created date
        //if (new DateTime($attendanceDate) >= new DateTime($userCreatedDate)) {
        $allowedFrom = ($userCreatedDate >= $firstDayOfCycle) ? $last30Days : $last7Days;

        $attendanceDateTime = new DateTime($attendanceDate);

        //  if ($attendanceDateTime >= new DateTime($allowedFrom) && $attendanceDateTime <= new DateTime($toDay)) {
        $attendanceQuery = "
                    SELECT 
                        attendances.id, 
                        attendances.fordate AS date, 
                        attendances.user_id AS user_id 
                    FROM attendances
                    WHERE DATE(attendances.fordate) = '$attendanceDate'  
                      AND attendances.user_id = $userId
                ";

        $attendanceData = $_DB->execute($attendanceQuery)->fetchAll("assoc");

        if (empty($attendanceData)) {
            if (!empty($leaveTypeId)) {
                $insertQueryLeave = "
                            INSERT INTO attendances 
                                (user_id, leavetype_id, fordate, status, logtime, final, created, modified, presenttype_id) 
                            VALUES 
                                ('$userId', '$leaveTypeId', '$attendanceDate', '$status', '$logTime', '0', '$dateTime', '$dateTime', '0')
                        ";

                $logQueries .= "Inserting Leave Record for User ID: $userId on Date: $attendanceDate...\n";
                $logQueries .= "Insert Query: \n$insertQueryLeave\n";

                if ($db->execute($insertQueryLeave)) {
                    $logQueries .= "Success: Record inserted.\n\n";
                    $processedUsers[$userId] = true;
                } else {
                    $logQueries .= "Failed: Could not insert the leave record.\n\n";
                }
            } elseif (!empty($presentTypeId)) {
                $insertQueryPresent = "
                            INSERT INTO attendances 
                                (user_id, fordate, status, logtime, final, created, modified, presenttype_id) 
                            VALUES 
                                ('$userId', '$attendanceDate', '$status', '$logTime', '0', '$dateTime', '$dateTime', '$presentTypeId')
                        ";

                $logQueries .= "Inserting Present Record for User ID: $userId on Date: $attendanceDate...\n";
                $logQueries .= "Insert Query: \n$insertQueryPresent\n";

                if ($db->execute($insertQueryPresent)) {
                    $logQueries .= "Success: Record inserted.\n\n";
                    $processedUsers[$userId] = true;
                } else {
                    $logQueries .= "Failed: Could not insert the present record.\n\n";
                }
            }
        } else {
            $logQueries .= "Record already exists in attendance table for User ID: $userId on Date: $attendanceDate.\n\n";
        }
        //  } else {
        // $logQueries .= "Attendance date for user $userId is out of the allowed range ($allowedFrom to $toDay).\n\n";
        //    }
        // } else {
        //     $logQueries .= "Skipping insertion: Attendance date ($attendanceDate) is before the user's creation date ($userCreatedDate) for User ID: $userId.\n\n";
        //  }
    } else {
        $logQueries .= "Skipping insertion: Missing attendance date or user creation date for User ID: $userId.\n\n";
    }

    $_UPDATEVAR['lastFormID'] = $formId;
}

$logQueries .= "Processing completed. Total users processed: " . count($processedUsers) . "\n\n";

$this->dropCompanyConnection($db->configKeyName);

$logFilePath = "/tmp/Samsung_at_data_log.txt";
$logFile = fopen($logFilePath, 'w');
fwrite($logFile, $logQueries);
fclose($logFile);

$config = array();
$config['to'] = array('alerts@mobisy.com', 'sadan.sindgi@mobisy.com');
$config['subject'] = "[Samsung TL] Attendance Data Insertion " . date('Y-m-d');
$config['message'] = "PFA";
$config['attachments'] = array($logFilePath);
$this->runAction('email', $config);

unlink($logFilePath);
