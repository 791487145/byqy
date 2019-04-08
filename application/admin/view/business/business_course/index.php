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
                                <label class="layui-form-label">所有课程</label>
                                <div class="layui-input-block">
                                    <select name="is_show">
                                        <option value="">是否显示</option>
                                        <option value="1">显示</option>
                                        <option value="0">不显示</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">所有分类</label>
                                <div class="layui-input-block">
                                    <select name="cid">
                                        <option value="">所有菜单</option>
                                        {volist name="cate" id="vo"}
                                        <option value="{$vo.id}">{$vo.html}{$vo.cate_name}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">课程名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="course_name" class="layui-input" placeholder="请输入课程名称">
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
        </div>
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">课程列表</div>
                <div class="layui-card-body">

                    <div class="layui-btn-container">

                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加课程</button>
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
                        <button class="layui-btn layui-btn-xs" onclick="javascript:window.location.href='{:Url('show_catalog')}?id={{d.id}}'">
                            <i class="fa fa-paste"></i> 查看
                        </button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('添加子章节','{:Url('catalog_create')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 添加章节
                        </button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                       <!-- <button class="layui-btn layui-btn-xs" onclick="javascript:window.location.href='{:Url('business.business_course_comment/index')}?cid={{d.id}}'">
                            <i class="fa fa-paste"></i> 查看评论
                        </button>-->
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
    layList.tableList('List',"{:Url('course_list')}",function (){
        return [
            {field: 'id', title: 'ID', sort: true,event:'id',width:'6%'},
            {field: 'course_name', title: '课程名称',edit:'course_name'},
            {field: 'pic', title: '课程封面',templet:'#pic'},
            {field: 'catalog', title: '章节数',templet:'#catalog',width:'6%'},
           /* {field: 'is_cost', title: '收费',templet:'#is_cost',width:'6%'},
            {field: 'price', title: '价格',templet:'#price',width:'6%'},*/
            {field: 'browse', title: '浏览量',templet:'#browse',width:'6%'},
          /*  {field: 'collect', title: '点赞',templet:'#like',width:'6%'},
            {field: 'like', title: '收藏',templet:'#collect',width:'6%'},*/
            {field: 'grade', title: '评分',templet:'#grade',width:'6%'},
            {field: 'is_show', title: '状态',templet:'#is_show',width:'6%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'25%'},
        ];
    });
    //自定义方法
    var action= {
        set_category: function (field, id, value) {
            layList.baseGet(layList.Url({
                c: 'business.business_course',
                a: 'set_category',
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
            layList.baseGet(layList.Url({c:'business.business_course',a:'set_show',p:{is_show:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'business.business_course',a:'set_show',p:{is_show:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //快速编辑
    layList.edit('course_name',function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'course_name':
                action.set_category('course_name',id,value);
                break;
            case 'sort':
                action.set_category('sort',id,value);
                break;
        }
    });

    //监听并执行排序
    layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'business.business_course',a:'delete',q:{id:data.id}});
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