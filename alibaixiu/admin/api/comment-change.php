<?php
require_once '../../functions.php';

if(empty($_POST['id']) || empty($_POST['status'])) {
  die('缺少必要的id参数');
}
//接受到了id和status
$id = $_POST['id'];
$status = $_POST['status'];

$affected_rows = xiu_execute("update comments
    set status = '{$status}' where id = {$id}");

header('Content-Type: application/json');
echo json_encode(array('success' => $affected_rows > 0));


