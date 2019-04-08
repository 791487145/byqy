<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?=$form->getTitle()?></title>
    <script src="{__ADMIN_PATH}/js/vue.min.js"></script>
    <link href="{__ADMIN_PATH}/js/iview.css" rel="stylesheet">
    <script src="{__ADMIN_PATH}/js/iview.min.js"></script>
    <script src="{__ADMIN_PATH}/js/jquery.min.js"></script>
    <script src="{__ADMIN_PATH}/js/province_city.js"></script>
    <script src="{__ADMIN_PATH}/js/form-create.min.js"></script>


    <link href="{__ADMIN_PATH}plug/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
    <link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
    <link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.min.js"></script>
    <script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
    <script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
    <script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
    <style>
        /*弹框样式修改*/
        .ivu-modal-body{padding: 5;}
        .ivu-modal-confirm-footer{display: none;}
        .ivu-date-picker {display: inline-block;line-height: normal;width: 280px;}
    </style>
</head>
<body>


<div class="form-group">
    <div class="col-md-12">
        <label style="color:#aaa">文章内容</label>
        <textarea type="text/plain" id="myEditor" style="width:100%;"></textarea>
    </div>
</div>

<script>

    var editor = document.getElementById('myEditor');
    editor.style.height = '300px';
    //实例化编辑器
    var um = UM.getEditor('myEditor',{
        //        fullscreen:true
    });
    /**
     * 获取编辑器内的内容
     * */
    function getContent() {
        return (UM.getEditor('myEditor').getContent());
    }
    function hasContent() {
        return (UM.getEditor('myEditor').hasContents());
    }
    function createFrame(title,src,opt){
        opt === undefined && (opt = {});
        return layer.open({
            type: 2,
            title:title,
            area: [(opt.w || 700)+'px', (opt.h || 650)+'px'],
            fixed: false, //不固定
            maxmin: true,
            moveOut:false,//true  可以拖出窗外  false 只能在窗内拖
            anim:5,//出场动画 isOutAnim bool 关闭动画
            offset:'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
            shade:0,//遮罩
            resize:true,//是否允许拉伸
            content: src,//内容
            move:'.layui-layer-title'
        });
    }

    /**
     * 编辑器上传图片
     * */
    $('.edui-icon-image').on('click',function (e) {
//                $('.upload').trigger('click');
        createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
    })





    formCreate.formSuccess = function(form,$r){
        <?=$form->getSuccessScript()?>
        //刷新父级页面
//        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
        //关闭当前窗口
//        var index = parent.layer.getFrameIndex(window.name);
//        parent.layer.close(index);
        //提交成功后按钮恢复
        $r.btn.finish();
    };

    (function () {
        var create = (function () {
            var getRule = function () {
                var rule = <?=json_encode($form->getRules())?>;
                rule.forEach(function (c) {
                    if ((c.type == 'cascader' || c.type == 'tree') && Object.prototype.toString.call(c.props.data) == '[object String]') {
                        if (c.props.data.indexOf('js.') === 0) {
                            c.props.data = window[c.props.data.replace('js.', '')];
                        }
                    }
                });
                return rule;
            }, vm = new Vue,name = 'formBuilderExec<?= !$form->getId() ? '' : '_'.$form->getId() ?>';
            var _b = false;
            window[name] =  function create(el, callback) {
                if(_b) return ;
                _b = true;
                if (!el) el = document.body;
                var $f = formCreate.create(getRule(), {
                    el: el,
                    form:<?=json_encode($form->getConfig('form'))?>,
                    row:<?=json_encode($form->getConfig('row'))?>,
                    submitBtn:<?=$form->isSubmitBtn() ? '{}' : 'false'?>,
                    resetBtn:<?=$form->isResetBtn() ? 'true' : '{}'?>,
                    iframeHelper:true,
                    upload: {
                        onExceededSize: function (file) {
                            vm.$Message.error(file.name + '超出指定大小限制');
                        },
                        onFormatError: function (file) {
                            vm.$Message.error(file.name + '格式验证失败');
                        },
                        onError: function (error) {
                            vm.$Message.error(file.name + '上传失败,(' + error + ')');
                        },
                        onSuccess: function (res) {
                            if (res.code == 200) {
                                return res.data.filePath;
                            } else {
                                vm.$Message.error(res.msg);
                            }
                        }
                    },
                    //表单提交事件
                    onSubmit: function (formData) {
                        $f.submitStatus({loading: true});
                        $.ajax({
                            url: '<?=$form->getAction()?>',
                            type: '<?=$form->getMethod()?>',
                            dataType: 'json',
                            data: formData,
                            success: function (res) {
                                if (res.code == 200) {
                                    vm.$Message.success(res.msg);
                                    $f.submitStatus({loading: false});
                                    formCreate.formSuccess && formCreate.formSuccess(res, $f, formData);
                                    callback && callback(0, res, $f, formData);
                                    //TODO 表单提交成功!
                                } else {
                                    vm.$Message.error(res.msg || '表单提交失败');
                                    $f.btn.finish();
                                    callback && callback(1, res, $f, formData);
                                    //TODO 表单提交失败
                                }
                            },
                            error: function () {
                                vm.$Message.error('表单提交失败');
                                $f.btn.finish();
                            }
                        });
                    }
                });
                return $f;
            };
            return window[name];
        }());

        window.$f = create();
//        create();
    })();
</script>
</body>
</html>