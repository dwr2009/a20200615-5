<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:68:"/www/wwwroot/tiaoshi.com/application/admin/view/extend/pay/juhe.html";i:1586548617;}*/ ?>
<div class="layui-tab-item">

    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
        <legend>聚合支付设置 <a target="_blank" href="https://juhe.fateqq.com/i/40625" class="layui-btn layui-btn-primary">点击进入注册</a></legend>
    </fieldset>
    
    <div class="layui-form-item">
        <label class="layui-form-label">支付商家编号：</label>
        <div class="layui-input-inline w400">
            <input type="text" name="pay[juhe][appid]" placeholder="" value="<?php echo $config['pay']['juhe']['appid']; ?>" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">支付商家密钥：</label>
        <div class="layui-input-inline w400">
            <input type="text" name="pay[juhe][appkey]" placeholder="" value="<?php echo $config['pay']['juhe']['appkey']; ?>" class="layui-input">
        </div>
    </div>
  <div class="layui-form-item">
        <label class="layui-form-label">网关地址：</label>
        <div class="layui-input-inline w400">
            <input type="text" name="pay[juhe][url]" placeholder="" value="<?php echo $config['pay']['juhe']['url']; ?>" class="layui-input">
        </div>
    </div>
   <div class="layui-form-item">
        <label class="layui-form-label">通道选择：</label>
        <div class="layui-input-inline w400">
            <input type="text" name="pay[juhe][tongdao]" placeholder="" value="<?php echo $config['pay']['juhe']['tongdao']; ?>" class="layui-input">
        </div>
    </div>
    
    
    
</div>