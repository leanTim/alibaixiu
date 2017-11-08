<?php

// 载入全部公共函数
require_once '../functions.php';
// 判断是否登录
xiu_get_current_user();

// 连接数据库
// 执行查询条数的语句
// 拿到查询结果
// 输出到指定位置

$all_posts_count = xiu_fetch_one('select count(1) as count from posts;')['count'];
$drafted_posts_count = xiu_fetch_one("select count(1) as count from posts where status = 'drafted';")['count'];
$categories_count = xiu_fetch_one('select count(1) as count from categories;')['count'];
$all_comments_count = xiu_fetch_one('select count(1) as count from comments;')['count'];
$held_comments_count = xiu_fetch_one("select count(1) as count from comments where status = 'held';")['count'];


?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Dashboard &laquo; Admin</title>
  <!-- / 指的是网站根目录 网站根目录是谁取决于 你 Apache 的配置 -->
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
  <script src="/static/assets/vendors/echarts/echarts.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <?php include 'inc/navbar.php'; ?>
    <div class="container-fluid">
      <div class="jumbotron text-center">
        <h1>One Belt, One Road</h1>
        <p>Thoughts, stories and ideas.</p>
        <p><a class="btn btn-primary btn-lg" href="post-add.php" role="button">写文章</a></p>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">站点内容统计：</h3>
            </div>
            <ul class="list-group">
              <li class="list-group-item" id="first-line"><strong><?php echo $all_posts_count; ?></strong>篇文章（<strong><?php echo $drafted_posts_count; ?></strong>篇草稿）</li>
              <li class="list-group-item" id="second-line"><strong><?php echo $categories_count; ?></strong>个分类</li>
              <li class="list-group-item" id="third-line"><strong><?php echo $all_comments_count; ?></strong>条评论（<strong><?php echo $held_comments_count; ?></strong>条待审核）</li>
            </ul>
          </div>
        </div>
        <div class="col-md-8" id="picturePie" style="height: 500px;"></div>
      </div>
    </div>
  </div>
  <?php $current_page = 'index'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script type="text/javascript">
   $(function () {
    var myChart = echarts.init(document.getElementById('picturePie'))
    var $one = $('#first-line').children().eq(0).text()
    var $two = $('#first-line').children().eq(1).text()
    var $three = $('#second-line').children().eq(0).text()
    var $four = $('#third-line').children().eq(0).text()
    var $five = $('#third-line').children().eq(1).text()
    //console.log($one.text());
     option = {
      title : {
          text: '站点内容统计',
          x:'center'
      },
      tooltip : {
          trigger: 'item',
          formatter: "{a} <br/>{b} : {c} ({d}%)"
      },
      legend: {
          orient: 'vertical',
          left: 'left',
          data: ['文章总数','草稿总数','分类','待审核评论','评论总数']
      },
      series : [
          {
              name: '内容统计',
              type: 'pie',
              radius : '55%',
              center: ['50%', '60%'],
              data:[
                  {value:$one, name:'文章总数'},
                  {value:$two, name:'草稿总数'},
                  {value:$three, name:'分类'},
                  {value:$five, name:'待审核评论'},
                  {value:$four, name:'评论总数'}
              ],
              itemStyle: {
                  emphasis: {
                      shadowBlur: 10,
                      shadowOffsetX: 0,
                      shadowColor: 'rgba(0, 0, 0, 0.5)'
                  }
              }
            }
        ]
      };

      myChart.setOption(option)
     })

  </script>
  <script>NProgress.done()</script>
</body>
</html>
