<?php defined('PT_ROOT') || exit('Permission denied');?>
<!DOCTYPE HTML>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="Cache-Control" content="max-age=0" forua="true"/>
<meta http-equiv="Cache-Control" content="no-transform"/>
<meta http-equiv="Cache-Control" content="no-siteapp"/>
<meta http-equiv="Cache-control" content="no-cache"/>
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
<title>操作提示</title>
<link href="https://cdn.bootcss.com/weui/1.1.2/style/weui.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="page msg_success js_show">
    <div class="weui-msg">
        <div class="weui-msg__icon-area">
            <i class="weui-icon-info-circle weui-icon_msg"></i>
        </div>
        <div class="weui-msg__text-area">
            <h2 class="weui-msg__title"><?php echo $message;?></h2>
			<?php if($waitsecond && $jumpurl!=''):?>
            <p class="weui-msg__desc"><a href="<?php echo $jumpurl;?>">没有自动跳转，点击返回</a></p>
			<?php endif;?>
        </div>
        <div class="">
            <a href="javascript:;" id="close" onclick="window.close();" class="weui-btn weui-btn_mini weui-btn_default">关闭本页</a>
        </div>
        <div class="weui-msg__extra-area">
            <div class="weui-footer">
                <p class="weui-footer__text">&copy; <?php echo $this->pt->config->get("tplconfig.banquan");?></p>
            </div>
        </div>
    </div>
</div>
<?php if($waitsecond):?>
<script type="text/javascript">
    var time = parseInt("<?php echo $waitsecond;?>");
    function redirect() {
        var url = "<?php echo $jumpurl;?>";
        if (url == '#back#') {
            window.history.back();
        } else if (url == '#close#') {
            window.close();
        } else {
            location.href = url;
        }
    }
    function change() {
        time--;
        if (time == 0) {
            clearInterval(t);
            redirect();
        }
    }
    t = setInterval('change()', 1000);
</script>
<?php endif;?>
</body>
</html>