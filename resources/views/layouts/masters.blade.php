<!DOCTYPE html>
<html >
<head>

	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title')</title>
	<link rel="stylesheet" type="text/css" href="{{asset('vue-lar/bootstrap/dist/css/bootstrap.css')}}">
	<link rel="stylesheet" type="text/css" href="{{asset('vue-lar/animate.css/animate.min.css')}}">
	@yield('css')
</head>
<body>
@yield('main')
<script type="text/javascript" src="{{asset('vue-lar/jquery/dist/jquery.min.js')}}"></script>
<script type="text/javascript" src="{{asset('vue-lar/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{asset('vue-lar/vue/dist/vue.js')}}"></script>
@yield('js')
</body>
</html>