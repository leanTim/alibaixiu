<?php
//载入函数
require_once '../../functions.php';

//处理分页
$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
$size = 20;
$skip = ($page - 1) * $size;

//获取数据
$comments = xiu_fetch_all("select
    comments.*,
    posts.title as post_title
  from comments
  inner join posts on comments.post_id = posts.id
  order by comments.created desc
  limit {$skip}, {$size}");

//查询总条数
$total_count = (int)xiu_fetch_one("select
    count(1) as i
from comments
inner join posts on comments.post_id = posts.id")['i'];
$total_pages = ceil($total_count / $size);

//序列化为json格式
$json_str = json_encode(array(
  'comments' => $comments,
  'total_pages' => $total_pages
   ));

//设置响应格式头数据格式为json
header('Content-Type: application/json');
echo $json_str;
