<?php

// 载入全部公共函数
require_once '../functions.php';
// 判断是否登录
xiu_get_current_user();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Comments &laquo; Admin</title>
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
        <h1>所有评论</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <div class="btn-batch" style="display: none">
          <button class="btn btn-info btn-sm">批量批准</button>
          <button class="btn btn-warning btn-sm">批量拒绝</button>
          <button class="btn btn-danger btn-sm">批量删除</button>
        </div>
        <ul id="showPage" class="pagination pagination-sm pull-right">
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>作者</th>
            <th>评论</th>
            <th>评论在</th>
            <th>提交于</th>
            <th>状态</th>
            <th class="text-center" width="140">操作</th>
          </tr>
        </thead>
        <tbody id="list"></tbody>
      </table>
    </div>
  </div>

  <?php $current_page = 'comments'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/jsrender/jsrender.js"></script>
  <script src="/static/assets/vendors/twbs-pagination/jquery.twbsPagination.js"></script>
  <script type="text/x-jsrender" id="comments_tmpl">
    {{for comments}}
    <tr>
      <td class="text-center"><input data-id="{{: id}}" type="checkbox"></td>
      <td>{{: author }}</td>
      <td>{{: content }}</td>
      <td>《{{: post_title }}》</td>
      <td>{{: created }}</td>
      <td>{{: status === 'approved' ? '已批准' : status === 'held' ? '待审' : '拒绝' }}</td>
      <td class="text-center">
        {{if status === 'held'}}
        <button class="btn btn-info btn-xs btn-change" data-status="approved" data-id="{{: id}}">批准</button>
        <button class="btn btn-warning btn-xs btn-change" data-status="rejected" data-id="{{: id}}">驳回</button>
        {{/if}}
        <button class="btn btn-danger btn-xs btn-delete" data-id="{{: id }}">删除</button>
      </td>
    </tr>
    {{/for}}}
  </script>
  <script>
    $(function ($) {
      var currentpage = 1
      function loadData (page) {
        //console.log(page)
        $.ajax({
          url: '/admin/api/comments.php',
          type: 'get',
          data: {page: page},
          dataType: 'json',
          success: function (res) {
            //console.log(res)
            //渲染数据
            //c从服务端获取到的数据res是一个对象，对象里有两个comments和total_pages  comments是一个数组，数组里的每条元素就是每一个tr的数据(对象的形式),
            //console.log(res.comments[0].content)
            //var context = {comments: res}
            //var context = {comments: res.comments}
            var html = $('#comments_tmpl').render(res)
            //console.log(html)
            //console.log(page)

            //将HTML输出到tbody中
            //TODO 从服务端获取到了数据在页面上没显现出来 why?
            $('#list').fadeOut(function () {
              $(this).html(html).fadeIn()
            })
            //========当能获取总页数的时候，再去展示一个正确的分页组件
            $showPage = $('#showPage')
            //先销毁之前的分页组件
            $showPage.twbsPagination('destroy')
            //extend  合并对象 相同的属性后面的值会覆盖前面的值
            //第一个参数传空对象的原因是防止defOptions被覆盖
            $showPage.twbsPagination($.extend({}, defOptions, {
              totalPages: res.total_pages,
              startPage: page,
              initiateStartPageClick: false
            }))

            //记录当前访问的页码
            currentpage = page

            //用cookie记住当前访问的是第几页
            var date = new Date

            date.setDate(date.getDate() + 7)
            //console.log(date.toGMTString())
            document.cookie = 'last_comment_visit_page='
              + page + ';exoires=' + date.toGMTString()
          }
        })
      }

      //====================分页功能==========
      //刚开始要显示一个假的分页，页总数要尽量大，等从服务端获取真的总页数之后再把这个假的分页删除。
      //console.log(page)
      var startPage = 1
      var cookies = document.cookie.split(';')
      //console.log(cookie)
      $(cookies).each(function (i, item) {
        var temp = item.trim().split('=')
        if (temp[0] === 'last_comment_visit_page') {
          startPage = parseInt(temp[1])
        }
      })

      var defOptions = {
        totalPages: 1000,
        startPage: startPage,
        first: '«',
        prev: '←',
        next: '→',
        last: '»',
        visiablePages: 5,
        onPageClick: function (e, page) {
          loadData(page)
        }
      }

      //这个插件的页码只能显示在ul里
      //twbsPagination 的作用是在指定元素上呈现一个分页组件
      $('#showPage').twbsPagination(defOptions)

      //================点击删除================
      $tbody = $('tbody')
      $tbody.on('click', '.btn-delete', function () {
        var $this = $(this)
        var delId = $this.data('id')
        $.get(
          '/admin/api/comment-delete.php',
          {id: delId},
          function (res) {
            res.success && loadData(currentpage)
          }
          )
      })

      //修改评论状态
      $tbody.on('click', '.btn-change', function () {
        $this = $(this)
        var id = $this.data('id')
        var status = $this.data('status')
        $.post('/admin/api/comment-change.php',
          {id: id,
          status: status},
          function (res) {
            res.success && loadData(currentpage)
          })
      })

        //显示与隐藏批量操作按钮
        $btnBatch = $('.btn-batch')
        var arr = []
        //采用事件委托的方式触发事件
        $tbody.on('change', 'input', function () {
          $this = $(this)
          console.log
          var id = $this.data('id')
          console.log(id)
          if($this.prop('checked')) {
            arr.push(id)
          }else {
            arr.splice(arr.indexOf(id), 1)
          }
          //如果arr中有元素 那么批量处理按钮显示
          console.log(arr)
          arr.length ? $btnBatch.fadeIn() : $btnBatch.fadeOut()
        })

      //显示与隐藏批量操作按钮
      $btnBatch = $('.btn-batch')
      var arr = []

      //采用事件委托的方式触发事件
      $tbody.on('change', 'input', function () {
          $this = $(this)
          var id = $this.data('id')
          if($this.prop('checked')) {
            arr.push(id)
          }else {
            arr.splice(arr.indexOf(id), 1)
          }
          //如果arr中有元素 那么批量处理按钮显示
          console.log(arr)
          arr.length ? $btnBatch.fadeIn() : $btnBatch.fadeOut()
      })

      var $tbodyInput = $('tbody input')
      $('thead input').on('click', function () {
        arr = []
        var checked = $(this).prop('checked')
        $tbodyInput.prop('checked', checked).trigger('change')
      })









    })


  </script>
  <script>NProgress.done()</script>



</body>
</html>
