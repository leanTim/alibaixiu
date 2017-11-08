<?php

// 载入全部公共函数
require_once '../functions.php';
// 判断是否登录
xiu_get_current_user();

//处理添加的函数
function add_user () {
  //处理文件上传
  if ($_FILES['avatar']['error'] !== 0) {
    $GLOBALS['pic_message'] = '请上传图片';
    return;
  }
  //上传的临时路径
  $temp_path = $_FILES['avatar']['tmp_name'];
  //文件的临时名称
  $file_name = $_FILES['avatar']['name'];
  $fin_path = '../static/assets/img/' . $file_name;
  if (!move_uploaded_file($temp_path, $fin_path)) {
    $GLOBALS['pic_message'] = '移动图片失败';
    return;
  }


  if (empty($_POST['email']) || empty($_POST['slug']) || empty($_POST['nickname']) || empty($_POST['password'])) {
    $GLOBALS['err_message'] = '请完整填写表单';
    return;
  }
  //获取提交的数据
  $email = $_POST['email'];
  $slug = $_POST['slug'];
  $nickname = $_POST['nickname'];
  $password = $_POST['password'];

  $sql = "insert into users values (null, '{$slug}', '{$email}', '{$password}', '{$nickname}', '{$fin_path}', null, 'activated');";
  $affected_rows = xiu_execute($sql);
  if ($affected_rows === 1) {
    $GLOBALS['success'] = '添加成功';
  }

}
//处理编辑事件的代码
function edit_user () {
   //处理文件上传
  if (empty($_POST['id']) || empty($_POST['email']) || empty($_POST['slug']) || empty($_POST['nickname']) || empty($_POST['password'])) {
    $GLOBALS['err_message'] = '请完整填写表单';
    return;
  }
  $email = isset($_POST['email']) ? $_POST['email'] : '';
  $slug = isset($_POST['slug']) ? $_POST['slug'] : '';
  $nickname = isset($_POST['nickname']) ? $_POST['nickname'] : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';
  $id = $_POST['id'];
  //var_dump()
  $sqli = "select * from users where id = {$id}";

  $user = xiu_fetch_one($sqli);
  $old_avatar = isset($user['avatar']) ? $user['avatar'] : '';
  //var_dump($user);
  //var_dump($old_avatar);
  //如果提交了图片，修改图片修改后的路径
  if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    //上传的临时路径
    $temp_path = $_FILES['avatar']['tmp_name'];
    //文件的临时名称
    $file_name = $_FILES['avatar']['name'];
    $fin_path = '../static/assets/img/' . $file_name;
    $moved = move_uploaded_file($temp_path, $fin_path);
  } else {
    $fin_path = $old_avatar;
  }


  $sql = "update users set slug = '{$slug}', email = '{$email}' ,avatar = '{$fin_path}', password = '{$password}' , nickname = '{$nickname}' where id = {$id}";
  //var_dump($email);
  $affacted_rows = xiu_execute($sql);
  if($affacted_rows === 1) {
    $GLOBALS['success'] = '编辑成功';
    //unset的作用是删除变量
    unset($_POST['email']);
    unset($_POST['slug']);
    unset($_POST['password']);
    unset($_POST['nickname']);
  }
}

//如果请求方式是post 调用函数
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //如果传了id，编辑 否则新增
  if (empty($_POST['id'])) {
    add_user();
  } else {
    edit_user();
  }
}

//；连接数据库，获取数据库的每一条数据 展现在table里
$users = xiu_fetch_all('select * from users')

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Users &laquo; Admin</title>
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
        <h1>用户</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <?php if (isset($err_message)): ?>
        <div class="alert alert-danger">
          <strong>错误！</strong><?php echo $err_message; ?>
        </div>
      <?php endif ?>
      <!-- 图片上传失败提示信息 -->
      <?php if (isset($pic_message)): ?>
        <div class="alert alert-danger">
          <strong>错误！</strong><?php echo $pic_message; ?>
        </div>
      <?php endif ?>
      <!-- 添加成功后才显示的内容 -->
      <?php if (isset($success)): ?>
        <div class="alert alert-warning">
          <strong>完美！</strong><?php echo $success; ?>
        </div>
      <?php endif ?>
      <div class="row">
        <div class="col-md-4">
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" novalidate enctype="multipart/form-data">
            <h2>添加新用户</h2>
            <!-- 隐藏域  在页面上不会显示出来 作用是提交当前的id 根据id的值辨别是提交还是编辑 -->
            <input type="hidden" id="hidden" name="id" value="0">
            <!-- 上传文件 -->
            <div class="form-group">
              <label for="avatar">头像</label>
              <!-- 当需要图片本地预览的时候 img标签显示 -->
              <input id="avatar" class="form-control" name="avatar" type="file" placeholder="头像">
              <img class="help-block yulan" style="display: none">
            </div>
            <div class="form-group">
              <label for="email">邮箱</label>
              <input id="email" class="form-control" name="email" type="email" placeholder="邮箱" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="slug">别名</label>
              <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" value="<?php echo isset($_POST['slug']) ? $_POST['slug'] : ''; ?>">
              <p class="help-block">https://zce.me/author/<strong>slug</strong></p>
            </div>
            <div class="form-group">
              <label for="nickname">昵称</label>
              <input id="nickname" class="form-control" name="nickname" type="text" placeholder="昵称" value="<?php echo isset($_POST['nickname']) ? $_POST['nickname'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="password">密码</label>
              <input id="password" class="form-control" name="password" type="text" placeholder="密码" value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''; ?>">
            </div>
            <div class="form-group">
              <button class="btn btn-primary btn-add" type="submit">添加</button>
              <button class="btn btn-default btn-cancel" type="submit" style="display : none;">取消</button>
            </div>
          </form>
        </div>
        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a id="btn_delete" class="btn btn-danger btn-sm" href="" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
               <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th class="text-center" width="80">头像</th>
                <th>邮箱</th>
                <th>别名</th>
                <th>昵称</th>
                <th>状态</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <tr>
                  <td class="text-center"><input data-id="<?php echo $user['id']; ?>" type="checkbox"></td>
                  <td class="text-center"><img class="avatar" src="<?php echo $user['avatar']; ?>"></td>
                  <td><?php echo $user['email']; ?></td>
                  <td><?php echo $user['slug']; ?></td>
                  <td><?php echo $user['nickname']; ?></td>
                  <td><?php echo $user['status']; ?></td>
                  <td class="text-center">
                    <button href="" class="btn btn-default btn-xs bianji" data-email="<?php echo $user['email']; ?>" data-slug="<?php echo $user['slug']; ?>" data-nickname="<?php echo $user['nickname']; ?>" data-password="<?php echo $user['password']; ?>" data-id="<?php echo $user['id']; ?>" data-src="<?php echo $user['avatar']; ?>">编辑</button>
                    <a href="/admin/user-delete.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-xs">删除</a>
                  </td>
              </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php $current_page = 'users'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
  //目的是有复选按钮被选中 批量删除按钮出现
    $(function ($) {
      //批量删除按钮
      var $btnDelete = $('#btn_delete')
      //定义一个保存被选中栏id的的数组
      //把数组里的值通过问号传参的方式传给服务端可以实现批量删除的效果，所以保存id
      var arr = []
      //通过事件委托的方式注册事件
      $('tbody').on('change', 'input', function () {
        //如果tbody里的
        var $this = $(this)
        var id = $this.data('id')
        //r如果有复选按钮被选中 把id放到数组里 否则从数组里删除
        if ($this.prop('checked')) {
          arr.push(id)
        } else {
          arr.splice(arr.indexOf(id), 1)
        }
        //如果arr中有数据 说明有复选按钮被选中 显示批量删除
        arr.length ? $btnDelete.show() : $btnDelete.fadeOut()
        //设置问号参数
        $btnDelete.attr('href', '/admin/user-delete.php?id=' + arr)

      })

        //全选和全不选
        //tbody;里的input按钮
        var $tbodyInput = $('tbody input')
        $('thead input').on('change', function () {
          arr = []
          var checked = $(this).prop('checked')
          $tbodyInput.prop('checked', checked)
            .trigger('change')
            //trigger的作用是在每一个匹配上的元素上触发此类事件
        })
      //处理编辑功能的js
      $('tbody').on('click', '.bianji', function () {
        var id = $(this).data('id')
        var email = $(this).data('email')
        var slug = $(this).data('slug')
        var nickname = $(this).data('nickname')
        var password = $(this).data('password')
        var src = $(this).data('src')
        //g改变新增栏的文本内容
        $('form h2').text('编辑')
        $('form button').eq(0).text('保存')
        $('form .btn-cancel').fadeIn()
        //设置隐藏域中的id是为了一会判断是添加还是编辑
        $('#hidden').val(id)
        $('#email').val(email)
        $('#slug').val(slug)
        $('#nickname').val(nickname)
        $('#password').val(password)
        $('.yulan').attr('src', src).fadeIn()

        //return false;
      })
      //取消编辑
      $('.btn-cancel').on('click', function () {
        $('form h2').text('添加新用户')
        $('form button').eq(0).text('添加')
        $('form .btn-cancel').fadeOut()
        //设置隐藏域中的id是为了一会判断是添加还是编辑
        $('#hidden').val(0)
        $('#email').val('')
        $('#slug').val('')
        $('#nickname').val('')
        $('#password').val('')
        return false
      })


      //处理本地图片预览的逻辑
      $('#avatar').on('change', function () {
        var file = $(this).prop('files')[0]
        console.log(file);
        console.log($(this).prop('files'));
        // 为这个文件对象创建一个 Object URL
        var url = URL.createObjectURL(file)
        $(this).siblings('.yulan').attr('src', url).fadeIn()

      })


    })


  </script>
  <script>NProgress.done()</script>
</body>
</html>
