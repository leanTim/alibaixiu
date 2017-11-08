<?php
/**
 * 公共函数封装
 */
require_once 'config.php';

session_start();

function xiu_get_current_user () {
  // 拿到客户端请求带来的小票
  // 找到那个对应箱子，取出标识用户是否登录的数据
  // 根据这个数据判断用户是否登录

  // 如果不存在 is_logged_in 或者值为 false
  if (empty($_SESSION['current_login_user'])) {
    // 没有登录
    header('Location: /admin/login.php');
    return;
  }
  return $_SESSION['current_login_user'];
}

/**
 * 建立一个与数据库的连接返回连接对象，注意需要自己关闭连接
 * @return [type] [description]
 */
function xiu_connect () {
  $conn = mysqli_connect(BAIXIU_DB_HOST, BAIXIU_DB_USER, BAIXIU_DB_PASS, BAIXIU_DB_NAME);
  if (!$conn) {
    // 后续代码没有执行的必要了
    die('<h1>连接错误 (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() . '</h1>');
  }
  return $conn;
}

/**
 * 执行一个 SQL 语句 得到执行结果(列表)
 * @param  [type] $sql [description]
 * @return [type]      [description]
 */
function xiu_fetch_all ($sql) {
  $conn = xiu_connect();

  $query = mysqli_query($conn, $sql);

  if (!$query) {
    // 查询失败
    return false;
  }

  while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
  }
  // $data => 全部数据

  // 释放结果集
  mysqli_free_result($query);

  // 断开与服务端的连接
  // 数据库连接是有限的，有必要在使用完了之后手动关闭掉
  mysqli_close($conn);

  return $data;
}

/**
 * 执行一个 SQL 语句 得到执行结果(单条)
 * @param  [type] $sql [description]
 * @return [type]      [description]
 */
function xiu_fetch_one ($sql) {
  return xiu_fetch_all($sql)[0];
}

/**
 * 执行一个非查询的查询语句，执行增删改语句
 * @param  [type] $sql [description]
 * @return [type]      [description]
 */
function xiu_execute ($sql) {
  $conn = xiu_connect();

  $query = mysqli_query($conn, $sql);

  if (!$query) {
    // 查询失败
    return false;
  }

  // 获取增删改语句受影响行数
  $affected_rows = mysqli_affected_rows($conn);

  // 增删改语句没有结果集需要释放
  // // 释放结果集
  // mysqli_free_result($query);

  // 断开与服务端的连接
  // 数据库连接是有限的，有必要在使用完了之后手动关闭掉
  mysqli_close($conn);

  return $affected_rows;
}

/**
 * 判断用户是否已经登录的函数
 */

function xiu_loggin_in ($url) {
  //$user = xiu_fetch_one("select * from users where email = '{$email}' limit 1;");
  if ($_SESSION['current_login_user']) {
    header('Location: /' . $url);
  }
}
