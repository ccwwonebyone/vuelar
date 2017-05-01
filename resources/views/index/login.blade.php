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
</style>
@endsection
@section('js')
<script type="text/javascript">
var app = new Vue({
    el:'#app',
    data:{
    	form:{username:'',password:''},
    	message:'欢迎来到vuelar！'
    },
    methods:{
    	submit:function(){
    		if(this.form.username == '' || this.form.password == ''){
    			this.message = '帐号或密码不能为空';
    			return false;
    		}
    		this.message = '正在请求验证！';
    		var _this= this;
    		$.ajax({
    			headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    			type:'post',
    			url:'checkLogin',
    			data:_this.form,
    			dataType:'json',
    			success:function(data){
    				_this.message = data.message;
    				if(data.status){
    					setTimeout(function(){
    						window.location.href = data.url;
    					},2000);
    				}
    			},
    			error:function(data){
    				_this.message = '网络错误';
    			}
    		})
    	}
    }
});
</script>
@endsection
@section('main')
<div id="app" class="row">
<div class="col-md-4"></div>
<div class="col-md-4">
	<div class="panel panel-default">
	<div class="panel-heading">
		<h3>vuelar<small> 登录</small></h3>
	</div>
	  <div class="panel-body">
	    <form>
	  		<div class="input-group">
  				<span class="input-group-addon">用户</span>
  				<input v-model="form.username" type="text" class="form-control" name="username">
			</div>

			<div class="input-group">
				<span class="input-group-addon">密码</span>
				<input v-model="form.password" class="form-control" type="password" name="password">
			</div>
			<div class="col-md-10">
				<div id="message" class="show-message">@{{message}}</div>
			</div>
			<div class="col-md-2">
				<span class="btn btn-primary" v-on:click="submit">登录</span>
			</div>
	    </form>
	  </div>
	</div>
</div>
<div class="col-md-4"></div>
</div>
@endsection