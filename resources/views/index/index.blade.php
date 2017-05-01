@extends('layouts.masters')
@section('title')首页@endsection
@section('css')
<style type="text/css">
.no-margin{
    margin: 0;
}
.left-menu{
    width: 300px;
    height: 95%;
    position: fixed;
    top: 50px;
    overflow-y: scroll;
    margin-bottom: 20px;
}
.database-list{
    padding-right: 0px;
    padding-left: 15px;
}
.database-header{
    margin-left: 20px;
}
.td-input{
    width: 150px;
}
body{
    padding-top: 50px;
    overflow-x: hidden;
}
.right-content{
    margin-left: 305px;
}
.second-menu{
    padding-left: 40px;
}
.no-padding{
    padding: 0;
}
.b-fixed{
    width: 100%;
    position: fixed;
    top:50px;
}
.td-width{
    width: 150px !important;
}
.page-header{
    margin-top: 0;
    padding-top: 50px;
}
.more-text{
    text-overflow:ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
</style>
@endsection
@section('js')
<script type="text/javascript">
var info = {
        webName:'SQLAR',      //网站名
        admin:true,         //管理员
        databases:[{id:'',database:'',show:true,introduce:'',comment:''}],          //数据库
        selectDatabase:{                                                        //数据库
                    id:'',
                    driver:'',
                    dsn:'',
                    database:'',
                    host:'',
                    username:'',
                    prefix:'',
                    charset:'',
                    port:'',
                    collation:'',
                    params:'',
                    comment:'',
                    introduce:''
                    },                //已选数据库
        selectTable:'',                                   //已选数据表
        showTableOptions:{limit:25,total:0,tableInfo:[],indexs:[]},
        tables:[{name:''}],          //显示数据表
        columns:[{field:'id',name:'ID',hidden:true},  //表格字段
                 {field:'Field',name:'字段'},
                 {field:'Comment',name:'注释'},
                 {field:'Type',name:'类型'},
                 {field:'Key',name:'主键'},
                 {field:'Null',name:'能否为空'},
                 {field:'Default',name:'默认值'},
                 {field:'introduce',name:'注解'}],
        showInput:{showColumn:'',table:'',index:''},  //需要变更为input的span
        tableInput:{index:'',tableColumn:''},                  //table需要变更为input的sapn
        data:[                                    //表格信息及字段信息
          {name:'',introduce:'',comment:'',
           data:[{id:1,Field:'',Type:'',Comment:'',Key:'',Null:'',Default:'',introduce:''}]}
        ],
        modal:{title:'新增',close:'取消',sub:'提交'},   //模态框内容
        showId:'',
        isdblclick:false,
        popover:{id:'',isShow:''},
        activeTarget:'',
        rightShow:{type:'',id:'',field:''},
        form:{                                           //表格内容
          url:'/insertDbInfo',
          data:{
            id:'',
            host:'localhost',
            database:'manp',
            username:'root',
            password:'123456',
            hostport:'3306',
            port:'3306',
            charset:'utf8'
          },
          items:[{label:'id',name:'id',hidden:true},    //表格展示
                 {label:'主机',name:'host'},
                 {label:'数据库',name:'database'},
                 {label:'用户名',name:'username'},
                 {label:'密码',name:'password'},
                 {label:'端口',name:'port'},
                 {label:'字符集',name:'charset'},
                 {label:'表前缀',name:'prefix'}]
        }
    };
var app = new Vue({
    el:'#app',
    data:info,
    created:function(){
      var user = document.getElementById('user');
      console.log(user);
    },
    methods:{
      submit:function(){
        $('#modalForm').submit();
      },
      /**
       * 改变span为input
       * @param  string field 改变字段
       * @param  string table 需要改变的表
       * @param  int index 数据位置
       * @return void
       */
      changeType:function(field,table,index){
        this.isdblclick = true;
        if(!this.admin) return false;  //管理员权限
        //只有注释和注解才可以改变
        if(field == 'Comment' || field == 'introduce'){
          this.showInput.showColumn = field;
          this.showInput.table = table;
          this.showInput.index = index;
          setTimeout(function(){
            var updateInput = document.getElementById('updateInput');
            updateInput.focus();
            updateInput.select();
          },300);
        }else{
          this.showInput.showColumn = '';
          this.showInput.table = '';
          this.showInput.index = '';
        }
      },
      /**
       * 修改数据库
       * @param  int id    数据表id
       * @param  string field 修改字段
       * @param  int inc   数据表的index
       * @param  int index 字段的index
       * @return void
       */
      updateData:function(table,id,field,inc,index,value){
        if(!this.admin) return false;  //管理员权限
        var updateInput = document.getElementById('updateInput');
        var val = $.trim(updateInput.value);
        this.showInput.showColumn = '';
        this.showInput.table = '';
        this.showInput.index = '';
        if(value == info) return false;

        this.data[inc].data[index][field] = val;
        //将变更信息交给后台处理
        var _this = this;
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          type:'post',
          url:'/update',
          dataType:'json',
          data:{
            table:table,
            id:id,
            field:field,
            info:val
          },
          success:function(data){
            _this.data[inc].data[index][field] = data.status ? val : value;
          }
        });
      },
      showdel:function(id){
      	this.showId = id;
      },
      hiddendel(){
      	this.showId = '';
      },
      del(id,table,index){
        var _this = this;
      	$.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          type:'post',
          url:'/del',
          dataType:'json',
          data:{
            table:table,
            id:id
          },
          success:function(data){
          	if(data.status){
                if(_this.databases[index].name == _this.selectDatabase.name){
                    var i = _this.databases.length;
                    if(i == 1){
                        getDbInfo('',_this);
                    }else{
                        getDbInfo(_this.databases[0].id,_this);
                    }
                }
          		_this.databases.splice(index,1);
          	}
          }
        });
      },
      showTableInput:function(index,column){
        if(!this.admin) return false;  //管理员权限
        this.tableInput.index = index;
        this.tableInput.tableColumn = column;
        setTimeout(function(){
            var updateInput = document.getElementById('introduce');
            updateInput.focus();
            updateInput.select();
          },300);
        console.log(index,column);
      },
      updateTableData:function(field,id,index,value,table){
        if(!this.admin) return false;  //管理员权限
        this.tableInput.index = '';
        this.tableInput.tableColumn = '';
        var val = document.getElementById('introduce').value;
        if(val == value) return false;
        var _this = this;
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          type:'post',
          url:'/update',
          dataType:'json',
          data:{
            table:table,
            id:id,
            field:field,
            info:val
          },
          success:function(data){
            if(table == 'table'){
                _this.data[index][field] = data.status ? val : value;
            }
            if(table == 'database'){
                _this.selectDatabase[field] = data.status ? val : value;
            }
          }
        });
      },
      initForm:function(){
        this.form.url = '/insertDbInfo';
        this.form.data = {
            id:'',
            host:'localhost',
            database:'sqlar',
            username:'root',
            password:'123456',
            hostport:'3306',
            port:'3306',
            charset:'utf8'
          }
      },
      changeFrom:function(){
        this.form.url = '/updateDatabaseConfig'
        this.form.data = this.selectDatabase;
      },
      updateDatabase:function(id){
        var _this = this;
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          type:'post',
          url:'/updateDatabase',
          dataType:'json',
          data:{
            id:id
          },
          success:function(data){
            getDbInfo(id,_this);
          }
        });
      },
      updateTable:function(id,databaseId){
        console.log(id,databaseId);
        var _this = this;
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          type:'post',
          url:'/updateTable',
          dataType:'json',
          data:{
            id:id
          },
          success:function(data){
            getDbInfo(databaseId,_this);
          }
        });
      },
      rightShowIt:function(type,id,target){
        console.log(type,id,target);
        this.activeTarget = target;
        this.rightShow.id = id;
        this.rightShow.type = type;
      },
      rightHideIt:function(){
        this.rightShow.id = '';
        this.rightShow.type = '';
      },
      showIntroduce:function(text,field,docId){
        if(field != 'introduce') return false;
        this.isdblclick = false;
        var _this = this;
        setTimeout(function(){
            if(_this.isdblclick) return false;

            if(_this.popover.isShow){
                $('#'+_this.popover.id).popover('destroy');
                _this.popover.isShow = false;
                if(_this.popover.id != docId){
                    $('#'+docId).popover({
                        content:text,
                        title:'introduce',
                        trigger:'click',
                        placement:'left'
                    });
                    $('#'+docId).popover('show');
                    _this.popover.id = docId;
                    _this.popover.isShow = true;
                }
            }else{
                $('#'+docId).popover({
                        content:text,
                        title:'introduce',
                        trigger:'click',
                        placement:'left'
                });
                $('#'+docId).popover('show');
                _this.popover.id = docId;
                _this.popover.isShow = true;
            }
        }, 300);
      },
      actvie:function(target){
        this.activeTarget = target;
      }
    }
});
console.log({{$id}});
getDbInfo("{{$id}}",app);
//监听滚动条
window.onscroll=function(){
    //滚动条位置
    var t = document.documentElement.scrollTop || document.body.scrollTop;
    var sh = document.documentElement.scrollHeight || document.body.scrollHeight;
    //窗口大小
    var h = window.innerHeight;
    if(h/(sh-t) > 0.8){
      console.log('yes');
      var tempData = [];
      app.showTableOptions.indexs.push(app.showTableOptions.indexs[app.showTableOptions.indexs.length-1]+1);
      app.data.push(app.tables[app.showTableOptions.indexs[app.showTableOptions.indexs.length-1]]);
      console.log(app.data);
    }
    console.log(t,sh,(sh-t),h,h/(sh-t));
}
function getDbInfo(id,vueEl) {
	$.ajax({
	  //laravel要求
	  headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
	  url:'/getInfo',
	  dataType:'json',
	  type:'post',
	  data:{
	    db_id:id
	  },
	  success:function(data){
	    vueEl.tables = data.tables;
	    vueEl.databases = data.databases;
	    //vueEl.data = [];

      for (var i = 0 ; i < vueEl.data.length; i++) {
        vueEl.showTableOptions.tableInfo[i] = vueEl.data[i].data.length;

      }

      for (var i = 0 ; i < vueEl.showTableOptions.tableInfo.length; i++) {
        vueEl.showTableOptions.total = vueEl.showTableOptions.total + vueEl.showTableOptions.tableInfo[0];
        vueEl.showTableOptions.indexs.push(i);
        console.log(vueEl.showTableOptions.total,vueEl.showTableOptions.limit);
        if(vueEl.showTableOptions.total > vueEl.showTableOptions.limit){
          break;
        }

      }
      var tempData = [];
      for (var indexs in vueEl.showTableOptions.indexs) {
        tempData.push(data.tables[indexs])
      }

      vueEl.data = tempData;
      console.log(vueEl.data);
      vueEl.selectDatabase={};
	    for (var i = data.databases.length - 1; i >= 0; i--) {
	      if(data.databases[i].show == true){
            vueEl.selectDatabase = data.databases[i];
	        break;
	      }
	    }
	  }
	})
}
</script>
@endsection
@section('main')
<div id="app">
<!-- app开始 -->
<!-- 顶部导航 -->
<div class="row">
    <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">@{{webName}}</a>
        </div>
        <div class="navbar-right">
          <button v-if="admin"
                  class="btn btn-default navbar-btn navbar-left"
                  v-on:click="initForm"
                  data-toggle="modal"
                  data-target="#myModal">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"> 数据库</span>
          </button>
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                  <input type="text" class="form-control" placeholder="搜索">
                </div>
            </form>
            <button class="btn btn-default navbar-btn navbar-right">
              <span class="glyphicon glyphicon-log-out" aria-hidden="true"> 登出</span>
            </button>
        </div>
      </div>
      </div>
    </nav>
</div>
<!-- 主体 -->
<div class="row">
<!-- 左侧导航 -->
<div class="left-menu">
      <a v-for="(database,index) in databases"
         class="list-group-item database-list"
         v-on:mouseover="showdel(database.id)"
         v-on:mouseout="hiddendel"
         v-bind:href="'/database/'+database.id"
         v-bind:class="database.show ? 'list-group-item-info' : 'list-group-item-danger'">
        <template>
            <a href="javascript:void(0);"
               v-if="admin"
               v-show="database.id == showId"
               v-on:click="del(database.id,'database',index)"
               class="badge btn btn-xs btn-danger">删除</a>
        </template>
        <template v-if="database.show">
            <h3 class="list-group-item-heading database-header">@{{database.database}}</h3>
            <a v-for="table in tables"
               v-on:click="actvie(table.name)"
               v-bind:href="'#'+table.name"
               class="list-group-item list-group-item-success second-menu"
               v-bind:class="activeTarget == table.name? 'active' : ''">@{{table.name}}</a>
        </template>
        <h4 v-else class="list-group-item-heading database-header">@{{database.database}}</h4>
      </a>
</div>
<!-- 右侧内容 -->
  <div class="col-lg-9 no-padding right-content" id="rightContent">
    <div>
      <div class="page-header">
        <h1 v-on:mouseover="rightShowIt('database',selectDatabase.id)"
            v-on:mouseout="rightHideIt">
            @{{selectDatabase.database}}
          <small v-on:dblclick="showTableInput(selectDatabase.id,'comment')">
          <input v-if="tableInput.index === selectDatabase.id && tableInput.tableColumn=='comment'"
                   v-bind:value="selectDatabase.comment"
                   v-on:keyup.enter = "updateTableData('comment',selectDatabase.id,selectDatabase.id,selectDatabase.comment,'database')"
                   v-bind:id="'introduce'"
                   class="form-control"
                   type="text" name="introduce">
            <span v-else>@{{selectDatabase.comment ? selectDatabase.comment : '--'}}</span>
            </small>
            <span v-show="rightShow.id == selectDatabase.id && rightShow.type == 'database'">
                <button v-if="admin"
                    class="btn btn-danger btn-sm"
                    v-on:click="changeFrom"
                    data-toggle="modal"
                    data-target="#myModal">
                    <span class="glyphicon glyphicon-edit"> 更新信息</span>
                </button>
                <button v-if="admin"
                    v-on:click="updateDatabase(selectDatabase.id)"
                    class="btn btn-danger btn-sm">
                    <span class="glyphicon glyphicon-edit"> 更新数据库</span>
                </button>
            </span>
        </h1>
        <blockquote>
            <p>
                类型 ：@{{selectDatabase.driver}} --
                主机 : @{{selectDatabase.host}} --
                端口 : @{{selectDatabase.port}} --
                字符集 ：@{{selectDatabase.charset}} --
                前缀 : @{{selectDatabase.prefix}}
            </p>
        </blockquote>
      </div>
      <p class="well" v-on:dblclick="showTableInput(selectDatabase.id,'introduce')">
            <input v-if="tableInput.index === selectDatabase.id && tableInput.tableColumn=='introduce'"
                   v-bind:value="selectDatabase.introduce"
                   v-on:keyup.enter = "updateTableData('introduce',selectDatabase.id,selectDatabase.id,selectDatabase.introduce,'database')"
                   v-bind:id="'introduce'"
                   class="form-control"
                   type="text" name="introduce">
            <span v-else>@{{selectDatabase.introduce ? selectDatabase.introduce : '--'}}</span>
      </p>
      <div v-for="(info,inc) in data">
        <!-- <div  style="height: 100px;width: 100%"></div> -->
        <div v-bind:id="info.name" class="page-header">
          <h3 v-on:mouseover="rightShowIt('table',info.id,info.name)"
              v-on:mouseout="rightHideIt">@{{info.name}}
          <small v-on:dblclick="showTableInput(inc,'comment')">
            <input v-if="tableInput.index === inc && tableInput.tableColumn=='comment'"
                   v-bind:value="info.comment"
                   v-on:keyup.enter = "updateTableData('comment',info.id,inc,info.comment,'table')"
                   v-bind:id="'introduce'"
                   class="form-control"
                   type="text" name="introduce">
            <span v-else>@{{info.comment ? info.comment : '--'}}</span>
          </small>
          <button v-if="admin"
                  v-show="rightShow.id == info.id && rightShow.type == 'table'"
                  v-on:click="updateTable(info.id,selectDatabase.id)"
                class="btn btn-danger btn-sm">
                <span class="glyphicon glyphicon-edit"> 更新数据表</span>
            </button>
          </h3>
        </div>
        <p class="well" v-on:dblclick="showTableInput(inc,'introduce')">
            <input v-if="tableInput.index === inc && tableInput.tableColumn=='introduce'"
                   v-bind:value="info.introduce"
                   v-on:keyup.enter = "updateTableData('introduce',info.id,inc,info.introduce,'table')"
                   v-bind:id="'introduce'"
                   class="form-control"
                   type="text" name="introduce">
            <span v-else>@{{info.introduce ? info.introduce  : '--'}}</span>
        </p>
        <table class="table table-bordered table-hover">
          <tr>
            <th v-for="column in columns"
                v-show="!column.hidden"
                v-bind:class="column.field == 'introduce' ? 'more-text' : ''">@{{column.name}}</th>
          </tr>
          <template v-for="(all,index) in info.data">
            <tr>
              <template v-for="column in columns">
                <td v-show="!column.hidden"
                    v-bind:class="column.field == 'introduce' || column.field == 'Comment' ? 'td-width' : ''"
                    v-on:dblclick="changeType(column.field,info.name,index)"
                    v-on:click="showIntroduce(all[column.field],column.field,column.field +''+inc +''+index)"
                    v-bind:id="column.field == 'introduce' ? column.field +''+inc +''+index : ''"
                    >
                    <transition
                      enter-active-class="animated zoomIn"
                      leave-active-class="animated fadeOut"
                    >
                    <input class="form-control td-input" type="text"
                           v-if="showInput.showColumn == column.field && showInput.table == info.name && showInput.index == index"
                           v-bind:value="all[column.field]"
                           v-on:keyup.enter="updateData('column',all.id,column.field,inc,index,all[column.field])"
                           v-bind:id="'updateInput'">
                    <span v-else v-bind:class="column.field == 'introduce' ? 'more-text' : ''">
                    @{{ all[column.field] != null ?
                        all[column.field].length > 19 ? all[column.field].substr(0,19)+'...' : all[column.field]
                    : '--' }}</span>
                    </transition>
                </td>
              </template>
            </tr>
          </template>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- 模态框 -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="myModalLabel">@{{modal.title}}</h4>
      </div>
      <div class="modal-body">
        <form role="form" method="post" v-bind:action="form.url" id="modalForm">
        {!! csrf_field() !!}
          <div v-for="item in form.items" class="form-group"
               v-bind:class="item.hidden ? 'hidden':''">
            <label v-bind:for="item.name" class="col-sm-2">@{{item.label}}：</label>
            <div class="col-sm-10">
              <input type="text" class="form-control"
                     v-bind:name="item.name"
                     v-bind:value="form.data[item.name]">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">@{{modal.close}}</button>
        <button type="button" class="btn btn-primary"  @click="submit">@{{modal.sub}}</button>
      </div>
    </div>
  </div>
</div>
<!-- app结束 -->
</div>
@endsection