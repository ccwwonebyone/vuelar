@extends('layouts.masters')
@section('title')首页@endsection
@section('css')
<style type="text/css">
.input-group{
  margin-bottom: 20px;
}
.show-message{
  padding-top: 5px;
}
.badge-other{
  float: right;
  /* margin: 0 20px; */
}
</style>
@endsection
@section('js')
<script type="text/javascript">
var install = new Vue({
  el:'#install',
  data:{
    webName:'vuelar',
    startCheckEnv:false,
    showStart:false,
    showDataBaseConfig:false,
    showDataBaseInstall:false,
    showEnd:false,
    database:{username:'root',password:'123456'},
    installDatabases:[],
    installInfo:{
      success:[],
      database:'',
      nextDatabase:''
    },
    env:[{type:'function',require:'openssl_open',result:false}]
  },
  methods:{
    start:function(){
      var _this = this;
      $.ajax({
        url:'/install/checkEnv',
        type:'get',
        dataType:'json',
        success:function(data){
          _this.env = data;
          console.log(data);
        }
      });
      this.showStart=false,
      this.startCheckEnv = true;
    },
    showSetDatabaseRow:function(){
      this.startCheckEnv = false;
      this.showDataBaseConfig = this.checkEnvResult ? true : false;
    },
    showDataBaseInstallRow:function(){
      if($.trim(this.database.username) != '' && $.trim(this.database.password) != ''){
        this.showDataBaseConfig = false;
        this.showDataBaseInstall = true;
        var _this = this;
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:'/install/getInstallDatabases',
          type:'get',
          dataType:'json',
          success:function(data){
            _this.installDatabases = data;
            console.log(data);
          }
        });
      }
    },
    startInstall:function(){
      var _this = this;
      var databaseInstall = new DatabaseInstall();
      databaseInstall.databases = [];
      for (var i = 0; i < this.installDatabases.length; i++) {
        databaseInstall.databases.push(this.installDatabases[i].name);
      }
      databaseInstall.installing = 0;
      databaseInstall.install(databaseInstall.databases[0],this.database.username,this.database.password,
        function(data,database,result){
          if(data.status){
            if(result == 'end'){
              _this.showDataBaseInstall = false;
              _this.showEnd = true;
            }
            for (var i = 0; i < _this.installDatabases.length; i++) {
              if(_this.installDatabases[i].name == database){
                _this.installDatabases[i].status = 'success';
                return false;
              }
            }
          }else{
            for (var i = 0; i < _this.installDatabases.length; i++) {
              if(_this.installDatabases[i].name == database){
                _this.installDatabases[i].status = 'fail';
                return false;
              }
            }
          }

          //console.log(data);
      },function(database,nextDatabase){
          _this.installDatabases[databaseInstall.installing-1].status = 'loading';
          _this.installInfo.database = database;
          _this.installInfo.nextDatabase = nextDatabase;
      });
    },
    toIndex:function(){
      window.location.href = "/login";
    }
  },
  computed:{
    checkEnvResult:function(){
      for (var i = 0; i < this.env.length; i++) {
        if(!this.env[i].result){
          return false;
        }
      }
      return true;
    }
  }
});
setTimeout(function(){
  install.showStart = true;
},300);

var DatabaseInstall = function(){};
DatabaseInstall.prototype.databases = ['vuelar','gitar'];
DatabaseInstall.prototype.installing = 0;
DatabaseInstall.prototype.getNextDatabase = function(){
  if(this.installing < this.databases.length){
    this.installing++;
    return this.databases[this.installing];
  }else{
    this.installing++;
    return false;
  }
}
DatabaseInstall.prototype.install = function(database,username,password,callback,sloveInfo){
  var _this = this;
  var nextDatabase = this.getNextDatabase();
  sloveInfo(database,nextDatabase);
  //console.log(nextDatabase);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:'/install/start',
    type:'post',
    dataType:'json',
    data:{
      username:username,
      password:password,
      database:database
    },
    success:function(data){
      if(nextDatabase){
        if(data.status){
          _this.install(nextDatabase,username,password,callback,sloveInfo);
        }
        var result = 'continue';
      }else{
        var result = 'end';
      }
      callback(data,database,result);
    }
  })
}


</script>
@endsection
@section('main')
<div id="install" class="container-fluid">

<!-- 开始 -->
<transition
  enter-active-class="animated zoomIn"
  leave-active-class="animated fadeOut"
>
<div class="row" v-if="showStart">
  <div class="col-md-4"></div>
  <div class="col-md-4">
    <div class="jumbotron">
      <h1 class="text-center">@{{webName}}</h1>
      <p class="text-center">网站安装</p>
      <p class="text-center"><a class="btn btn-primary btn-lg" v-on:click="start" role="button">开始</a></p>
    </div>
  </div>
  <div class="col-md-4"></div>
</div>
</transition>
<!-- 环境监测 -->
<transition
  enter-active-class="animated zoomIn"
  leave-active-class="animated fadeOut"
>
<div class="row" v-if="startCheckEnv">

  <div class="col-md-4"></div>
  <div class="col-md-4">
    <div class="page-header">
      <h1>环境监测 <small>for  vuelar</small></h1>
    </div>
    <ul class="list-group">

      <li v-for="require in env" class="list-group-item "
          v-bind:class="require.result ? 'list-group-item-success' : 'list-group-item-danger'">
        <span class="badge">@{{require.result ? '√' : '×'}}</span>
        @{{require.type}}：@{{require.require}}</li>
    </ul>
    <div class="col-md-10">
        <div id="message" class="show-message">监测结果
          <small>@{{checkEnvResult ? '符合' : '失败'}}</small></div>
      </div>
      <div class="col-md-2">
        <span class="btn"
          v-bind:class="checkEnvResult ? 'btn-success' : 'btn-warning'"
          :disabled="!checkEnvResult"
          v-on:click="showSetDatabaseRow"
        >
          下一步
        </span>
    </div>
  </div>
  <div class="col-md-4"></div>
</div>
</transition>
<!-- 数据库设置 -->
<transition
  enter-active-class="animated zoomIn"
  leave-active-class="animated fadeOut"
>
<div class="row" v-if="showDataBaseConfig">
  <div class="col-md-4"></div>
  <div class="col-md-4">
    <div class="page-header">
      <h1>数据库 <small>用户-密码</small></h1>
    </div>
    <form>
      <div class="input-group">
        <span class="input-group-addon">用户名</span>
        <input v-model="database.username" class="form-control" type="text" name="password">
      </div>
      <div class="input-group">
        <span class="input-group-addon">密&nbsp;&nbsp;&nbsp; 码</span>
        <input v-model="database.password" class="form-control" type="password" name="password">
      </div>
      <div class="col-md-10">
        <div id="message" class="show-message">数据库设置 <small>utf8</small></div>
      </div>
      <div class="col-md-2">
        <span class="btn btn-primary" v-on:click="showDataBaseInstallRow">下一步</span>
      </div>
    </form>
  </div>
  <div class="col-md-4"></div>
</div>
</transition>
<!-- 开始安装数据库 -->
<transition
  enter-active-class="animated zoomIn"
  leave-active-class="animated fadeOut"
>
<div class="row" v-if="showDataBaseInstall">
  <div class="col-md-4"></div>
  <div class="col-md-4">
    <div class="page-header">
      <h1>开始安装 <small>@{{installInfo.database}}</small></h1>
    </div>
    <ul class="list-group">
      <li v-for="installDatabase in installDatabases" class="list-group-item list-group-item-info">
        <span class="badge-other" v-if="installDatabase.status!='wait'">
          <img width="20" height="20" v-bind:src="installDatabase.status == 'loading' ? '/images/loading.gif' : installDatabase.status == 'success' ? '/images/success.png' : '/images/wrong.png'">
        </span>
        @{{installDatabase.name}}</li>
    </ul>
    <div class="col-md-10">
        <div id="message" class="show-message">即将安装 <small>@{{installInfo.nextDatabase}}</small></div>
    </div>
    <div class="col-md-2">
      <span class="btn btn-primary" v-on:click="startInstall">开始安装</span>
    </div>
  </div>
  <div class="col-md-4"></div>
</div>
</transition>
<!-- 结束安装 -->
<transition
  enter-active-class="animated zoomIn"
  leave-active-class="animated fadeOut"
>
<div class="row" v-if="showEnd">
  <div class="col-md-4"></div>
  <div class="col-md-4">
    <div class="jumbotron">
      <h1 class="text-center">vuelar</h1>
      <p class="text-center">网站安装成功</p>
      <p class="text-center"><a class="btn btn-primary btn-lg" v-on:click="toIndex"
            role="button">开始体验吧！</a></p>
    </div>
  </div>
  <div class="col-md-4"></div>
</div>
</transition>
</div>
@endsection