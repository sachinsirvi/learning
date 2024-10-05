<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include_once('/home/sachinsirvi/script/database.php');

ini_set('memory_limit', '-1');

$companyQuery = "SELECT id, name, dbname, domainname FROM companies WHERE companystatus_id = 7 AND is_deleted = 0 ";
$companies = $db->query($companyQuery, "bizomsignup");

$columns = "Company ID,Company Name,Domain Name,Setting Name,Level,Level ID,Value";
$csvBasePath = "/tmp/allsettings"; 
$csvExtension = ".csv";
$fileCounter = 1; 

$csvPath = $csvBasePath . '_' . $fileCounter . $csvExtension;  
$csvFile = fopen($csvPath, "w");
fwrite($csvFile, $columns . "\r\n");

$rowCount = 1; 
$maxRows = 1048576 - 1;  

foreach ($companies as $company) {
    $companyId = $company['id'];
    $companyName = $company['name'];
    $dbName = $company['dbname'];
    $domainName = $company['domainname'];

    // Settings 
    $settingQuery = "
        SELECT 
            settings.id AS settingId,
            settings.company_id AS companyId,
            settings.name AS settingName,
            settings.value AS settingValue,    
            settingoverrides.designation_id AS designationId,        
            settingoverrides.role_id AS roleId,        
            settingoverrides.warehouse_id AS warehouseId,       
            settingoverrides.value AS overrideValue

        FROM settings
        LEFT JOIN settingoverrides ON settings.id = settingoverrides.setting_id
        WHERE settings.is_active = 1 
          AND settings.isjson = 0 
          AND settings.company_id =  $companyId";

    $settingData = $db->query($settingQuery, $dbName);

    $companyRowCount = count($settingData);
    

    if ($rowCount + $companyRowCount > $maxRows) {
        fclose($csvFile);  
        $fileCounter++; 
        $csvPath = $csvBasePath . '_' . $fileCounter . $csvExtension; 
        $csvFile = fopen($csvPath, "w"); 
        fwrite($csvFile, $columns . "\r\n"); 
        $rowCount = 1;  
    }

    if (!empty($settingData)) {
        foreach ($settingData as $setData) {
            $settingName = $setData['settingName'];
            $level = "Company";
            $levelId = "";
            $value = $setData['settingValue'];

          /*  if (!empty($setData['userId'])) {
                $level = "User";
                $levelId = $setData['userId'];
                $value = $setData['overrideValue'];
            } */
             if (!empty($setData['designationId'])) {
                $level = "Designation";
                $levelId = $setData['designationId'];
                $value = $setData['overrideValue'];
            } elseif (!empty($setData['roleId'])) {
                $level = "Role";
                $levelId = $setData['roleId'];
                $value = $setData['overrideValue'];
            } elseif (!empty($setData['warehouseId'])) {
                $level = "Warehouse";
                $levelId = $setData['warehouseId'];
                $value = $setData['overrideValue'];
            }

            if (is_null($value)) {
                $value = '';
            } else {
                $value = '"' . str_replace('"', '""', $value) . '"'; 
            }

            $row = array($companyId, $companyName, $domainName, $settingName, $level, $levelId, $value);
            $rowString = implode(',', $row);			
            fwrite($csvFile, $rowString . "\r\n");

            $rowCount++;  
        }
    }
}

fclose($csvFile);

$mail = new PHPMailer();

$to = "sachin.sirvi@mobisy.com","sarath.nair@mobisy.com","smitesh@mobisy.com";
$cc = "product@mobisy.com";

$mail->setFrom('do-not-reply@bizom.in');
$mail->addAddress($to);
$mail->Subject = "Bizom: Allsettings Data";
$mail->Body = "Hi All, \n Please find the attachment of Allsettings details.";

for ($i = 1; $i <= $fileCounter; $i++) {
    $csvFilePath = $csvBasePath . '_' . $i . $csvExtension;
    $mail->addAttachment($csvFilePath);
}

if ($mail->send()) {
    echo "Email sent.";
} else {
    echo "Email not sent.";
}

?>
