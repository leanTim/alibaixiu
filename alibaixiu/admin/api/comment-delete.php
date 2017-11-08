<?php
require_once '../../functions.php';

if (empty($_GET['id'])) {
  die('缺失必要的ID参数');
}

$id = $_GET['id'];

$rows = xiu_execute ("delete from comments where id in (" . $id . ")");
header('Content-Type: application/json');

echo json_encode(array('success' => $rows > 0));
