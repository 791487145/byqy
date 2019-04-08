{extend name="public/container"}
{block name="head_top"}
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
{/block}
{block name="content"}
<div class="row" style="width: 100%">
    <div class="col-sm-12">
        <div class="col-sm-2 panel panel-default " style="width: 100%">
            <!-- col-sm-10 panel panel-default news-right -->
            <div class="col-sm-12 panel panel-default" >
                <div class="panel-heading">文章内容编辑</div>
                <div class="panel-body">
                    <form class="form-horizontal" id="signupForm">
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-addon">课程名称</span>
                                    <input maxlength="64" name="title" class="layui-input"  value="{$course_name}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-addon">章节标题</span>
                                    <input maxlength="64" placeholder="请在这里输入标题" name="catalog_title" class="layui-input" id="catalog_title" value="">
                                    <input type="hidden" name="course_id" value="{$course_id}" id="course_id">
                                    <input type="hidden" name="type" value="{$type}" id="type">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label style="color:#aaa">章节简介</label>
                                <textarea  id="synopsis" name="synopsis" class="layui-input" style="height:80px;resize:none;line-height:20px;color:#333;"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="form-control" style="height:auto">
                                    <label style="color:#ccc">图文封面</label>
                                    <div class="row nowrap">
                                        <div class="col-xs-3" style="width:160px">

                                            <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('/public/system/module/wechat/news/images/image.png')">
                                                <input value="" type="hidden" name="local_url">
                                            </div>

                                        </div>
                                        <div class="col-xs-6">
                                            <input type="file" class="upload" name="image" style="display: none;" id="image" />
                                            <br>
                                            <a class="btn btn-sm add_image upload_span">上传图片</a>
                                            <br>
                                            <br>
                                        </div>
                                    </div>
                                    <input type="hidden" name="image" id="image_input" value=""/>
                                    <p class="help-block" style="margin-top:10px;color:#ccc">封面大图片建议尺寸：200像素 * 116像素</p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="input-group">
                                <label >试听：</label>
                                <input type="radio" name="is_free" class="layui-radio " value="0" checked>  否
                                <input type="radio" name="is_free" class="layui-radio" value="1" style="padding-left: 20px">  是
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label style="color:#aaa">章节内容</label>
                                <textarea type="text/plain" id="myEditor" style="width:100%;"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-9">
                                    <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {/block}
    {block name="script"}
    <script>
        var editor = document.getElementById('myEditor');
        //editor.style.height = '300px';
        editor.style.width='100%';
        //实例化编辑器
        var um = UM.getEditor('myEditor',{
                   //fullscreen:true
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
        function changeIMG(index,pic){
            $(".image_img").css('background-image',"url("+pic+")");
            $(".active").css('background-image',"url("+pic+")");
            $('#image_input').val(pic);
        };
        /**
         * 上传图片
         * */
        $('.upload_span').on('click',function (e) {
//                $('.upload').trigger('click');
            createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
        })

        /**
         * 编辑器上传图片
         * */
        $('.edui-icon-image').on('click',function (e) {
//                $('.upload').trigger('click');
            createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
        })

        /**
         * 提交图文
         * */
        $('.save_news').on('click',function(){
            var list = {};
            list.catalog_title = $('#catalog_title').val();/* 标题 */
            list.content = getContent();/* 内容 */
            list.file = $('#image_input').val();/* 图片 */
            list.description=$('#synopsis').val();
            list.cid=$('#course_id').val();
            list.type=$('#type').val();
            list.is_free = $("input[name='is_free']:checked").val();
            var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
            var objExp=new RegExp(Expression);
            if(list.catalog_title == ''){
                $eb.message('error','请输入课程标题');
                return false;
            }

            if(list.content == ''){
                $eb.message('error','请输入内容');
                return false;
            }
            var data = {};
            $.ajax({
                url:"{:Url('add_catalog')}",
                data:list,
                type:'post',
                dataType:'json',
                success:function(re){
                    if(re.code == 200){
                        data[re.data] = list;
                        $eb.message('success',re.msg);
                        setTimeout(function (e) {
                            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                           parent.layer.close(parent.layer.getFrameIndex(window.name));
                        },600);
                    }else{
                        $eb.message('error',re.msg);
                    }
                }
            })
        });
        $('.article-add ').on('click',function (e) {
            var num_div = $('.type-all').children('div').length;
            if(num_div > 7){
                $eb.message('error','一组图文消息最多可以添加8个');
                return false;
            }
            var url = "/public/system/module/wechat/news/images/image.png";
            html = '';
            html += '<div class="news-item transition active news-image" style=" margin-bottom: 20px;background-image:url('+url+')">'
            html += '<input type="hidden" name="new_id" value="" class="new-id">';
            html += '<span class="news-title del-news">x</span>';
            html += '</div>';
            $(this).siblings().removeClass("active");
            $(this).before(html);
        })
        $(document).on("click",".del-news",function(){
            $(this).parent().remove();
        })
        $(document).ready(function() {
            var config = {
                ".chosen-select": {},
                ".chosen-select-deselect": {allow_single_deselect: true},
                ".chosen-select-no-single": {disable_search_threshold: 10},
                ".chosen-select-no-results": {no_results_text: "沒有找到你要搜索的分类"},
                ".chosen-select-width": {width: "95%"}
            };
            for (var selector in config) {
                $(selector).chosen(config[selector])
            }
        })
    </script>
    {/block}