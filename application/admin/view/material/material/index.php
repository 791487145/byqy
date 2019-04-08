{extend name="public/container"}
{block name="content"}

<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">所有分类</label>
                                <div class="layui-input-block">
                                    <select name="cid">
                                        <option value="">所有分类</option>
                                        {volist name="cate" id="vo"}
                                        <option value="{$vo.id}" {eq name="$where.cid" value="$vo.id"}selected="selected"{/eq}>{$vo.title}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create',array('cid'=>$where.cid))}',{w:1100,h:760})">添加素材</button>
        </div>
        <!--产品列表-->
        <div class="ibox-content">
            <table class="footable table table-striped  table-bordered " data-page-size="20">
                <thead>
                <tr>
                    <th class="text-center" width="5%">id</th>
                    <th class="text-center" width="10%">标题</th>
                    <th class="text-left" >作者</th>
                    <th class="text-left" >分类</th>
                    <th class="text-center" width="15%">添加时间</th>
                    <th class="text-center" width="15%">操作</th>
                </tr>
                </thead>
                <tbody>
                {volist name="list" id="vo"}
                <tr>
                    <td>{$vo.id}</td>
                    <td>{$vo.title}</td>
                    <td>{$vo.author}</td>
                    <td>{$vo.catename}</td>
                    <td>{$vo.add_time|date="Y-m-d H:i:s",###}</td>

                    <td class="text-center">
                        <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('create',array('id'=>$vo['id'],'cid'=>$where.cid))}',{w:1100,h:760})"><i class="fa fa-paste"></i> 编辑</button>
                        <button class="btn btn-warning btn-xs del_news_one" data-id="{$vo.id}" type="button" data-url="{:Url('delete',array('id'=>$vo['id']))}" ><i class="fa fa-warning"></i> 删除
                    </td>
                </tr>
                {/volist}
                </tbody>
            </table>
        </div>
        <div style="margin-left: 10px">
            {include file="public/inner_page"}
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    var name;
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();


    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
    $('.del_news_one').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });
    //快速编辑
    layList.edit('cate_name',function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'cate_name':
                action.set_category('cate_name',id,value);
                break;
            case 'sort':
                action.set_category('sort',id,value);
                break;
        }
    });
</script>
{/block}
