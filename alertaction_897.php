/*
[EWS-Vietnam]Order State Change 
st alert id:-664
st alert action id:- 632
st alert assignees id:-1382
st alert schedules id:-1331

Live Alert ID: 885
Live Alert Action ID: 897
Live Alert Assignee ID: 22234
Live Alert Schedule ID: 1539
Cron Time : every 2 hours
alerts.query : {"query": "SELECT '{{lastExecutedOn}}' as lastExecutedOn"}
Alertassignees.tmpvariables:{"lastExecutedOn":"2024-09-26 12:00:00"}
Alertactions.alertactiontype_id :  7 transaction

Ticket - https://projects.zoho.in/portal/mobisy#taskdetail/15779000002340099/15779000003899297/15779000008739420

*/

echo '<pre>';

$lastExecutedOn = $datum[0]['lastExecutedOn'];
$currentDateTime = date("Y-m-d H:i:s", strtotime('+1 hour 30 minutes')); //this is vietnam time

$query = "SELECT users.username, users.password FROM users WHERE id = 1";
$userForToken = $_DB->query($query)->fetchAll('assoc');

$fields = array();
$fields['username'] = $userForToken[0]['username'];
$fields['accesskey'] = $userForToken[0]['password'];
$fields = json_encode($fields);

$access_token = execCurl('/oauth/getAccessTokenForEllCron', 'POST', $fields, null, true);
$access_token = json_decode($access_token, true);

if (empty($access_token)) {
    exit('Unable to retrieve access token');
}

$token = $access_token["Data"]["Token"];

$ordersQuery = "
    SELECT orders.id, orders.amount, credit_limit, (credit_limit - balance) as Remaining_Limit
    FROM orders
    LEFT JOIN outletbalances ON outletbalances.foroutlet_id = orders.fromoutlet_id AND orders.outlet_id = outletbalances.outlet_id
    WHERE orders.created BETWEEN '$lastExecutedOn' AND '$currentDateTime' 
      AND credit_limit > 0 
      AND orderstate_id IN (1,2)
    ORDER BY orders.id
      ";

$ordersData = $_DB->execute($ordersQuery)->fetchAll('assoc');

if ($ordersData) {
    $logData .= "API Used => orders/changeDirectOrderStateApi " . "\n\n";

    foreach ($ordersData as $order) {
        $orderId = $order['id'];
        $orderAmount = $order['amount'];
        $remainingLimit = $order['Remaining_Limit'];
        $creditLimit = $order['credit_limit'];

        $newOrderStateId = ($remainingLimit <= $creditLimit) ? 2 : 4;

        $postData = array(
            'data[url]' => 'orders/changeDirectOrderStateApi',
            'data[postparams]' => array(
                'order_id' => "$orderId",
                'neworderstate_id' => "$newOrderStateId"
            )
        );

        $logData .= "Order: $orderId | Order_Amount : $orderAmount | Credit_limit : $creditLimit | Remaining_Limit : $remainingLimit  " . "\n";

        $apiResponse = execCurl("/users/forwardRequest/?access_token=$token", 'POST', $postData);

        $_UPDATEVAR['lastExecutedOn'] = $currentDateTime;

        $logData .= print_r($apiResponse, true) . "\n\n";
    }

    $filePath = "/tmp/ews_vtnm_orderstate_Logs.txt";
    file_put_contents($filePath, $logData);

    $config = array();
    $config['to'] = array('alerts@mobisy.com','poornima.i@mobisy.com');
    $config['subject'] = "EWS_Vietnam Order State Change " . date("Y-m-d");
    $config['message'] = "PFA";
    $config['attachments'] = array($filePath);

    $this->runAction('email', $config);
    
    unlink($filePath);
}
