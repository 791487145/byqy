{extend name="public/container"}
{block name="content"}

<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
<!--            <div class="layui-card">-->
<!--                <div class="layui-card-header">搜索条件</div>-->
<!--                <div class="layui-card-body">-->
<!--                    <form class="layui-form layui-form-pane" action="">-->
<!--                        <div class="layui-form-item">-->
<!--                            <div class="layui-inline">-->
<!--                                <label class="layui-form-label">所有课程</label>-->
<!--                                <div class="layui-input-block">-->
<!--                                    <select name="is_show">-->
<!--                                        <option value="">是否显示</option>-->
<!--                                        <option value="1">显示</option>-->
<!--                                        <option value="0">不显示</option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="layui-inline">-->
<!--                                <label class="layui-form-label">所有分类</label>-->
<!--                                <div class="layui-input-block">-->
<!--                                    <select name="cid">-->
<!--                                        <option value="">所有菜单</option>-->
<!---->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="layui-inline">-->
<!--                                <label class="layui-form-label">课程名称</label>-->
<!--                                <div class="layui-input-block">-->
<!--                                    <input type="text" name="course_name" class="layui-input" placeholder="请输入课程名称">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="layui-inline">-->
<!--                                <div class="layui-input-inline">-->
<!--                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">-->
<!--                                        <i class="layui-icon layui-icon-search"></i>搜索</button>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </form>-->
<!--                </div>-->
<!--            </div>-->
        </div>
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">代理列表</div>
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        序号越低，代理层级越高，列表[代理名称],[代理成本价优惠比例],可进行快速修改,双击或者单击进入编辑模式,失去焦点可进行自动保存
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="layui-btn-container">

                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加代理</button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="pic">
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.pic}}">
                    </script>
                    <script type="text/html" id="is_show">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_show' lay-text='显|隐'  {{ d.is_show == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="pid">
                        <a href="{:Url('index')}?pid={{d.id}}">查看</a>
                    </script>
                    <script type="text/html" id="act">


                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>

                        <button class="layui-btn layui-btn-xs" lay-event='delstor'>
                            <i class="fa fa-warning"></i> 删除
                        </button>
                    </script>
                </div>
            </div>
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

    //加载列表
    layList.tableList('List',"{:Url('reseller_list')}",function (){
        return [
            {field: 'resell_level', title: '序号',width:'8%'},
            {field: 'resell_name', title: '代理名称',edit:'resell_name'},
            {field: 'reseller_brokerage', title: '代理成本价优惠比例',edit:'reseller_brokerage'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'25%'},
        ];
    });
    //自定义方法
    var action= {
        set_category: function (field, id, value) {
            layList.baseGet(layList.Url({
                c: 'reseller.reseller_setting',
                a: 'set_reseller',
                q: {field: field, id: id, value: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        },
    }
    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
    layList.switch('is_show',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'zhishi.course',a:'set_show',p:{is_show:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'zhishi.course',a:'set_show',p:{is_show:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //快速编辑
    layList.edit('resell_name',function (obj) {
        var id=obj.data.id,value=obj.value;
        action.set_category('resell_name',id,value);
    });

    layList.edit('reseller_brokerage',function (obj) {
        var id=obj.data.id,value=obj.value;
        if(isNaN(value)){
            layList.msg('请输入正确数字');
            return;
        }
        if(value>100||value<0){
            layList.msg('代理成本价比例最低为0,最大不能超过100');
            return;
        }
        action.set_category('reseller_brokerage',id,value);
    });
    //监听并执行排序
    layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'zhishi.course',a:'delete',q:{id:data.id}});
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                })
                break;
            case 'open_image':
                $eb.openImage(data.pic);
                break;
        }
    })
</script>
{/block}
