<?php

$lang = lang(@$_GET['lang']);

//вся логика остается в модуле авторизации для возможности подключения как отдельного модуля на сайте
$u[2] = @$_GET['action'];
include(ROOT_DIR.'modules/login.php');

$api['post'] = $abc['post'];

if (access('user auth')) {
	$api['success'] = 1;

	$api['data'] = array(
		//переадресация на урл
		array(
			'method' => 'location',
			'content' => get_url('profile')
		)
	);
}
else {
	//покажем ошибку
	//dd($api);
	//$api['error_text'] = html_render('form/messages',$api['post']['message']);
	//todo когда в api_response(response) добавить всплівающее окно то тут поправить чтобы выводился массив сообшений
	$api['error_text'] = $api['post']['message'][0];
}