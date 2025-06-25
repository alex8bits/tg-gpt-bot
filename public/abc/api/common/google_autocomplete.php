<?php

//автозаполнение при поиске по карте гугла
//нужно влкючить Places API
//https://console.cloud.google.com/apis/library/places-backend.googleapis.com
//чтобы это работало, нужно в гугле прописывать ип а не сайт

/*
 v1.4.7 - admin/template2
 v1.4.45 - urldecode
*/

//определение значений формы
$fields = array(
	'search'		=>	'text',
);
//создание массива $post
$post = form_smart($fields,stripslashes_smart($_GET)); //print_r($post);

$api['list'] = array();

if ($post['search']) {
	$post['search'] = urlencode($post['search']);
	$url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' . $post['search'] . '&key=' . $config['google_map_key'] . '&language=en';
	if ($json = @file_get_contents($url)) {
		//log_add('google.txt',$json);
		//die($json);
		$data = json_decode($json, true);
		if(@$data['predictions']) {
			foreach ($data['predictions'] as $k=>$v) {
				$api['list'][] = array(
					'id'=>$v['description'],
					'text'=>$v['description']
				);
			}
		}
	}
	//v1.4.45 - urldecode
	$api['list'][] = array(
		'id'=>urldecode($post['search']),
		'text'=>urldecode($post['search'])
	);
}
