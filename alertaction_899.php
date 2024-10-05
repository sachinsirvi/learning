/*Allcompanies Userworkflow Script 
----------------------------------
ST alerts id  -665
ST alertactions id  - 633
ST alertassignees id  - 1384
ST alertschedules id  - 1333
----------------------------
alerts id  -887
alertactions id  - 899
alertassignees id  - 22337
alertschedules id  - 1540  “At 05:37 on Monday.”
--------------------------------------------
*/

echo '<pre>';

$companyQuery = "SELECT id, name, dbname, domainname 
                 FROM bizomsignup.companies 
                 WHERE companystatus_id = 7 
                 AND (is_deleted = 0 OR is_blocked = 0) 
                 ORDER BY id 
                ";
                 
$companies = $_DB->query($companyQuery)->fetchAll('assoc');

$columns = "CompanyID, CompanyName, DomainName, RoleID, RoleName, sale, order,salereturn, stockoutlet, collection, adhocads, addoutlet,activity, attendance, attendanceimage,collaterals,pop,claims,genericform,outletageing ,vendorbid, activitypicture,activitycomment,activityform,outletenrollment, emailtoretailer ,lms ,readgeolocation ,assettrack ,orderfulfil,outletedit, tasks ,assetorder ,existingasset,reqfordiscount,assetaudit,addtask , emailpacreport,searchoutletonline ,shownearestoutlets ,enrolwithtarget,sampling ,enterskuprice,edituserlocvisibility ,printertype ,primaryorder,primarysalereturn,primarycollections,txnsinsinglescreen,edituserinfo,beatplanning,stock_transfer,primaryactivityform ,reporteeview ,trainer ,taskapprover,addevent,secondarygrn,claim_auditor";

$columns = explode(",", $columns);
$columnString = implode(',', $columns);
$csvPath = "/tmp/ModulewiseUser.csv";
$csvFile = fopen($csvPath, "w");
fwrite($csvFile, $columnString . "\r\n");

foreach ($companies as $company) {
    $companyId = $company['id'];
    $dbName = $company['dbname'];
    $domainName = $company['domainname'];

    try {
        $_DB->query("USE `$dbName`");

        $userquery = "SELECT companies.id, companies.name AS 'companyname', manageroles.role_id, roles.name, 
                      COUNT(DISTINCT CASE WHEN userworkflows.sale = 1 THEN userworkflows.user_id END ) AS 'sale__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.order = 1 THEN userworkflows.user_id END ) AS 'order__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.salereturn = 1 THEN userworkflows.user_id END ) AS 'salereturn__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.stockatoutlet = 1 THEN userworkflows.user_id END ) AS 'stockatoutlet__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.collection = 1 THEN userworkflows.user_id END ) AS 'collection__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.adhocads = 1 THEN userworkflows.user_id END ) AS 'adhocads__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.addoutlet = 1 THEN userworkflows.user_id END ) AS 'addoutlet__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.activity = 1 THEN userworkflows.user_id END ) AS 'activity__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.attendance = 1 THEN userworkflows.user_id END ) AS 'attendance__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.attendanceimage = 1 THEN userworkflows.user_id END ) AS 'attendanceimage__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.collaterals = 1 THEN userworkflows.user_id END ) AS 'collaterals__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.pop = 1 THEN userworkflows.user_id END ) AS 'pop__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.claims = 1 THEN userworkflows.user_id END ) AS 'claims__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.genericform = 1 THEN userworkflows.user_id END ) AS 'genericform__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.outletageing = 1 THEN userworkflows.user_id END ) AS 'outletageing__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.vendorbid = 1 THEN userworkflows.user_id END ) AS 'vendorbid__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.activitypicture = 1 THEN userworkflows.user_id END ) AS 'activitypicture__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.activitycomment = 1 THEN userworkflows.user_id END ) AS 'activitycomment__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.activityform = 1 THEN userworkflows.user_id END ) AS 'activityform__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.outletenrollment = 1 THEN userworkflows.user_id END ) AS 'outletenrollment__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.emailtoretailer = 1 THEN userworkflows.user_id END ) AS 'emailtoretailer__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.lms = 1 THEN userworkflows.user_id END ) AS 'lms__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.readgeolocation = 1 THEN userworkflows.user_id END ) AS 'readgeolocation__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.assettrack = 1 THEN userworkflows.user_id END ) AS 'assettrack__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.orderfulfil = 1 THEN userworkflows.user_id END ) AS 'orderfulfil__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.outletedit = 1 THEN userworkflows.user_id END ) AS 'outletedit__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.tasks = 1 THEN userworkflows.user_id END ) AS 'tasks__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.assetorder = 1 THEN userworkflows.user_id END ) AS 'assetorder__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.existingasset = 1 THEN userworkflows.user_id END ) AS 'existingasset__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.reqfordiscount = 1 THEN userworkflows.user_id END ) AS 'reqfordiscount__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.assetaudit = 1 THEN userworkflows.user_id END ) AS 'assetaudit__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.addtask = 1 THEN userworkflows.user_id END ) AS 'addtask__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.emailpacreport = 1 THEN userworkflows.user_id END ) AS 'emailpacreport__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.searchoutletonline = 1 THEN userworkflows.user_id END ) AS 'searchoutletonline__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.shownearestoutlets = 1 THEN userworkflows.user_id END ) AS 'shownearestoutlets__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.enrolwithtarget = 1 THEN userworkflows.user_id END ) AS 'enrolwithtarget__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.sampling = 1 THEN userworkflows.user_id END ) AS 'sampling__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.enterskuprice = 1 THEN userworkflows.user_id END ) AS 'enterskuprice__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.edituserlocvisibility = 1 THEN userworkflows.user_id END ) AS 'edituserlocvisibility__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.printertype = 1 THEN userworkflows.user_id END ) AS 'printertype__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.primaryorder = 1 THEN userworkflows.user_id END ) AS 'primaryorder__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.primarysalereturn = 1 THEN userworkflows.user_id END ) AS 'primarysalereturn__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.primarycollections = 1 THEN userworkflows.user_id END ) AS 'primarycollections__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.txnsinsinglescreen = 1 THEN userworkflows.user_id END ) AS 'txnsinsinglescreen__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.edituserinfo = 1 THEN userworkflows.user_id END ) AS 'edituserinfo__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.beatplanning = 1 THEN userworkflows.user_id END ) AS 'beatplanning__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.stock_transfer = 1 THEN userworkflows.user_id END ) AS 'stock_transfer__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.primaryactivityform = 1 THEN userworkflows.user_id END ) AS 'primaryactivityform__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.reporteeview = 1 THEN userworkflows.user_id END ) AS 'reporteeview__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.trainer = 1 THEN userworkflows.user_id END ) AS 'trainer__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.taskapprover = 1 THEN userworkflows.user_id END ) AS 'taskapprover__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.addevent = 1 THEN userworkflows.user_id END ) AS 'addevent__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.secondarygrn = 1 THEN userworkflows.user_id END ) AS 'secondarygrn__', 
                      COUNT(DISTINCT CASE WHEN userworkflows.claim_auditor = 1 THEN userworkflows.user_id END ) AS 'claim_auditor__' 
                      FROM userworkflows 
                      LEFT JOIN users ON users.id = userworkflows.user_id 
                      LEFT JOIN manageroles ON manageroles.user_id = users.id 
                      LEFT JOIN companies ON companies.id = users.company_id 
                      LEFT JOIN roles ON roles.id = manageroles.role_id 
                      WHERE manageroles.role_id > 0 
                      AND users.active = 1 
                      AND companies.id = $companyId 
                      GROUP BY manageroles.role_id";
 
        $orderdata = $_DB->query($userquery)->fetchAll('assoc');
  
        if (empty($orderdata)) {
            continue;
        }

        foreach ($orderdata as $orders) {
            $rows = array(
                $orders['id'], $orders['companyname'], $domainName, $orders['role_id'], $orders['name'],
                $orders['sale__'], $orders['order__'], $orders['salereturn__'], $orders['stockatoutlet__'], 
                $orders['collection__'], $orders['adhocads__'], $orders['addoutlet__'], $orders['activity__'],
                $orders['attendance__'], $orders['attendanceimage__'], $orders['collaterals__'], $orders['pop__'], 
                $orders['claims__'], $orders['genericform__'], $orders['outletageing__'], $orders['vendorbid__'], 
                $orders['activitypicture__'], $orders['activitycomment__'], $orders['activityform__'], 
                $orders['outletenrollment__'], $orders['emailtoretailer__'], $orders['lms__'], $orders['readgeolocation__'], 
                $orders['assettrack__'], $orders['orderfulfil__'], $orders['outletedit__'], $orders['tasks__'], 
                $orders['assetorder__'], $orders['existingasset__'], $orders['reqfordiscount__'], $orders['assetaudit__'], 
                $orders['addtask__'], $orders['emailpacreport__'], $orders['searchoutletonline__'], 
                $orders['shownearestoutlets__'], $orders['enrolwithtarget__'], $orders['sampling__'], $orders['enterskuprice__'], 
                $orders['edituserlocvisibility__'], $orders['printertype__'], $orders['primaryorder__'], 
                $orders['primarysalereturn__'], $orders['primarycollections__'], $orders['txnsinsinglescreen__'], 
                $orders['edituserinfo__'], $orders['beatplanning__'], $orders['stock_transfer__'], 
                $orders['primaryactivityform__'], $orders['reporteeview__'], $orders['trainer__'], 
                $orders['taskapprover__'], $orders['addevent__'], $orders['secondarygrn__'], $orders['claim_auditor__']
            );

            $columnStringTwo = implode(',', $rows);
            fwrite($csvFile, $columnStringTwo . "\r\n");
        }
    } catch (Exception $e) {
        error_log("Error processing company $companyId: " . $e->getMessage());
        continue; 
    }
}

fclose($csvFile);

$config = array();
$config['to'] = array('product@mobisy.com');
$config['cc'] = array('alerts@mobisy.com');
$config['subject'] = "Bizom: Module-wise Users Data";
$config['message'] = "Hi All, \n Please find the attachment of modulewise users details "; 
$config['attachments'] = array($csvPath);
$this->runAction('email', $config);

