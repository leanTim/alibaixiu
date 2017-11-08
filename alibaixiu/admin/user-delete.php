<?php
//载入公共文件
require_once '../functions.php';

if (empty($_GET['id'])) {
    die('缺失必要的ID');
}

$id = $_GET['id'];
var_dump($id);
// $sql = "delete from users where id = {$id}";
$sql = 'delete from users where id in('. $id .')';
xiu_execute($sql);
header('Location: /admin/users.php');