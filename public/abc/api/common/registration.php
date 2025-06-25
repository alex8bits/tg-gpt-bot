<?php

//регистрация

//$lang = lang(@$_GET['lang']);

//вся логика остается в модуле регистрации для возможности подключения как отдельного модуля на сайте
include(ROOT_DIR.'modules/registration.php');

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
	$api['data'] = array(
		//общее количество товара
		array(
			'selector' => '#registration .message_box',
			'method' => 'html',
			'content' => html_render('form/messages',$api['post']['message'])
		),
		array(
			'selector' => '#registration .message_box',
			'method' => 'scroll'
		)
	);
}