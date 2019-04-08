{extend name="public/container"}
{block name="head_top"}
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="layui-btn-container" style="padding-left: 15px">
                        <button type="button" class="layui-btn layui-btn-sm" onclick="javascript:window.location.href='{:Url('business.business_course/index')}'">返回课程列表</button>
                    </div>
                    <div class="m-b m-l">
                        <form action="" class="form-inline">
                            <select name="is_reply" aria-controls="editable" class="form-control input-sm">
                                <option value="">评论状态</option>
                                <option value="0" {eq name="where.is_reply" value="0"}selected="selected"{/eq}>未回复</option>
                                <!--                                <option value="1" {eq name="where.is_reply" value="1"}selected="selected"{/eq}>客户已评价且管理员未回复</option>-->
                                <option value="1" {eq name="where.is_reply" value="1"}selected="selected"{/eq}>已回复</option>
                            </select>
                            <div class="input-group">
                                <input type="text" name="comment" value="{$where.comment}" placeholder="请输入评论内容" class="input-sm form-control" size="38"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="ibox">
                    {volist name="list" id="vo"}
                    <div class="col-sm-12">
                        <div class="social-feed-box">
                            <div class="pull-right social-action dropdown">
                                <button data-toggle="dropdown" class="dropdown-toggle btn-white" aria-expanded="false">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu m-t-xs">
                                    {if condition="$vo['is_reply'] eq 2"}
                                    <li><a href="#" class="reply_update"  data-url="{:Url('set_reply')}"  data-content="" data-id="{$vo['id']}">编辑</a></li>
                                    {else/}
                                    <li><a href="#" class="reply"  data-url="{:Url('set_reply')}" data-id="{$vo['id']}">回复</a></li>
                                    {/if}
                                    <li><a href="#" class="delete" data-url="{:Url('delete',array('id'=>$vo['id']))}">删除</a></li>
                                </ul>
                            </div>
                            <div class="social-avatar">
                                <a href="" class="pull-left">
                                    <img alt="image" src="{$vo.headimgurl}">
                                </a>
                                <div class="media-body">
                                    <a href="#">
                                        {$vo.nickname}
                                    </a>
                                    <small class="text-muted">{$vo.add_time|date='Y-m-d H:i:s',###}</small>
                                </div>
                            </div>
                            <div class="social-body">
                                <p>{$vo.comment}
                                    <?php $image = json_decode($vo['pics'],true);?>
                                    {if condition="$image"}
                                    {volist name="image" id="v"}
                                    <img src="{$v}" alt="" class="open_image" data-image="{$v}" style="width: 50px;height: 50px;cursor: pointer;">
                                    {/volist}
                                    {else/}
                                    [无图]
                                    {/if}
                                </p>
                            </div>
                            {if count($vo.replys)}
                            {foreach name='$vo.replys' item='r'}
                            <div class="social-footer">
                                <div class="social-comment">
                                    <div class="media-body">回复时间：<small class="text-muted">{$r.merchant_reply_time|date='Y-m-d H:i:s',###}</small></div>
                                    <div class="media-body">
                                        <p>{$r.merchant_reply_content}</p>
                                    </div>
                                </div>
                            </div>
                           {/foreach}
                           {/if}
                        </div>
                    </div>
                    {/volist}
                </div>

                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.delete').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    window.location.reload();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
    $('.reply').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url'),rid =_this.data('id');
        $eb.$alert('textarea',{'title':'请输入回复内容','value':''},function(result){
            $eb.axios.post(url,{content:result,id:rid}).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.swal(res.data.msg);
                    window.location.reload();
                }else
                    $eb.swal(res.data.msg);
            });
        })
    });
    $('.reply_update').on('click',function (e) {
        window.t = $(this);
        var _this = $(this),url =_this.data('url'),rid =_this.data('id'),content =_this.data('content');
        $eb.$alert('textarea',{'title':'请输入回复内容','value':content},function(result){
            $eb.axios.post(url,{content:result,id:rid}).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.swal(res.data.msg);
                }else{
                    $eb.swal(res.data.msg);
                }
            });
        })
    });
</script>
{/block}
