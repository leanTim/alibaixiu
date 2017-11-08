<?php

// 载入全部公共函数
require_once '../functions.php';
// 判断是否登录
xiu_get_current_user();

//从数据库中获取分类的信息
$categories = xiu_fetch_all("select * from categories");



//处理分类的逻辑
$where = '1=1'; //一个小技巧 便于处理后面的字符串拼接
//如果通过url传入了category='id' 参数并且值不为all的话 执行下面代码
if (isset($_GET['category']) && $_GET['category'] !== 'all') {
  $where .= " and posts.category_id = " . $_GET['category'];
}
//
if (isset($_GET['status']) && $_GET['status'] !== 'all') {
  $where .= " and posts.status = '{$_GET['status']}'";
}

//处理分页的逻辑
$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
//规定每页包含20条数据
$size = 20;

$offset = ($page - 1) * $size;

//查询数据库中一共有多少条数据
$total_count = (int)xiu_fetch_one("select
  count(1) as num
from posts
inner join users on posts.user_id = users.id
inner join categories on posts.category_id = categories.id
where " . $where)['num'];
var_dump($total_count);
//总页数

$total_page = (int)ceil($total_count / $size);
var_dump($total_page);
//第一个显示的页码
$begin = $page - 2 < 1 ? 1 : $page - 2;
$end = $begin + 4;

if ($end > $total_page) {
  $end = $total_page;
  $begin = $end - 4 < 1 ? 1 : $end - 4;
}


//从数据库中获取数据
$sql = "select
  posts.id,
  posts.title,
  posts.created,
  posts.status,
  users.nickname as user_name,
  categories.name as category_name
from posts
inner join users on posts.user_id = users.id
inner join categories on posts.category_id = categories.id
where " . $where . "
order by posts.created desc
limit " . $offset . " , " . $size . "";
//获取数据库中所有数据
$posts = xiu_fetch_all($sql);

/**
 *将英文转化为中文内的函数
 *
 */
function change_to_ch ($status) {
  $dict = array(
    'published' => '已发布',
    'drafted' => '草稿',
    'trashed' => '回收站');
  return isset($dict[$status]) ? $dict[$status] : '未知';
}

/**
 * 改变日期格式的函数
 */

function change_styleof_time ($date) {
  //将日期变成时间戳格式
  $time = strtotime($date);
  return date('Y年m月n日H:i:s', $time);


}




?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <?php include 'inc/navbar.php'; ?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>所有文章</h1>
        <a href="post-add.php" class="btn btn-primary btn-xs">写文章</a>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <a id="delete-all" class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
          <select name="category" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($categories as $item): ?>
              <option value="<?php echo $item['id']; ?>"<?php echo isset($_GET['category']) && $_GET['category'] === $item['id'] ? ' selected' : ''; ?>><?php echo $item['name']; ?></option>
            <?php endforeach ?>
            <option value="">未分类</option>
          </select>
          <select name="status" class="form-control input-sm">
            <option value="all">所有状态</option>
            <option value="drafted"<?php echo isset($_GET['status']) && $_GET['status'] === 'drafted' ? ' selected' : ''; ?>>草稿</option>
            <option value="published"<?php echo isset($_GET['status']) && $_GET['status'] === 'published' ? ' selected' : ''; ?>>已发布</option>
            <option value="trashed"<?php echo isset($_GET['status']) && $_GET['status'] === 'trashed' ? ' selected' : ''; ?>>回收站</option>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <li><a href="/admin/posts.php?page=<?php echo $page - 1; ?>">上一页</a></li>
          <?php for($i = $begin; $i <= $end; $i++): ?>

          <li <?php echo $page === $i ? 'class="active"' : ''; ?>><a href="/admin/posts.php?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>"><?php echo $i; ?></a></li>
          <?php endfor; ?>
          <li><a href="/admin/posts.php?page=<?php echo $page + 1; ?>">下一页</a></li>
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $item): ?>
            <tr>
              <td class="text-center"><input data-id="<?php echo $item['id']; ?>" type="checkbox"></td>
              <td><?php echo $item['title']; ?></td>
              <td><?php echo $item['user_name']; ?></td>
              <td><?php echo $item['category_name']; ?></td>
              <td class="text-center"><?php echo change_styleof_time($item['created']); ?></td>
              <td class="text-center"><?php echo change_to_ch($item['status']); ?></td>
              <td class="text-center">
                <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>
                <a href="/admin/posts-del.php?id=<?php echo $item['id'] ?><?php echo isset($_GET['page']) ? '&page='.$_GET['page'] : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?><?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?>" class="btn btn-danger btn-xs">删除</a>
              </td>
          </tr>
          <?php endforeach ?>

        </tbody>
      </table>
    </div>
  </div>

  <?php $current_page = 'posts'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function ($) {
      //获取批量删除按钮
      var $deleteAll = $('#delete-all')
      var arr = []
      $('tbody').on('change', 'input', function () {
        $this = $(this)
        var id = $this.data('id')
        if ($this.prop('checked')) {
          arr.push(id)
        }else {
          arr.splice(arr.indexOf(id), 1)
        }
        //console.log(arr)
        arr.length ? $deleteAll.fadeIn() : $deleteAll.fadeOut()
        $deleteAll.attr('href', '/admin/posts-del.php?id=' + arr)
      })

      //全选和全部选
      var $tbodyInput = $('tbody input')
      $('thead input').on('change', function () {
        arr = []
        var checked = $(this).prop('checked')
        $tbodyInput.prop('checked', checked).trigger('change')
      })


    })



  </script>
  <script>NProgress.done()</script>
</body>
</html>
