<?php

//session_start();

// загрузка функций **********************************************************
//require_once(ROOT_DIR.'functions/admin_func.php');	//функции админки
require_once(ROOT_DIR . 'functions/auth_func.php');	//функции авторизации
require_once(ROOT_DIR . 'functions/common_func.php');	//общие функции
//require_once(ROOT_DIR.'functions/file_func.php');	//функции для работы с файлами
//require_once(ROOT_DIR.'functions/html_func.php');	//функции для работы нтмл кодом
require_once(ROOT_DIR . 'functions/form_func.php');	//функции для работы со формами
//require_once(ROOT_DIR.'functions/image_func.php');	//функции для работы с картинками
require_once(ROOT_DIR . 'functions/lang_func.php');	//функции словаря
//require_once(ROOT_DIR.'functions/mail_func.php');	//функции почты
require_once(ROOT_DIR . 'functions/mysql_func.php');	//функции для работы с БД
//require_once(ROOT_DIR.'functions/string_func.php');	//функции для работы со строками


//аутентификация - создание массива с данными пользователя
$user = user('auth');

//определение значений формы
$fields = array(
	'edit'	=> 'text',
	'content'	=> 'text',
);
//создание массива $post
$post = form_smart($fields,stripslashes_smart($_POST)); //print_r($post);

$edit = explode('|',$post['edit']);

if (!isset($edit[3])) die('error#1');
if (access('editable '.$edit[1])==false) die('error#2');

//словарь
if ($edit[1]=='dictionary') {
	$lang = array();
	include(ROOT_DIR.'files/languages/'.$edit[0].'/'.$edit[1].'/'.$edit[2].'.php');
	$lang[$edit[2]][$edit[3]] = htmlspecialchars_decode($post['content']);
	$str = '<?php'.PHP_EOL;
	$str.= '$lang[\''.$edit[2].'\'] = array('.PHP_EOL;
	foreach ($lang[$edit[2]] as $k=>$v) {
		$str.= "	'".$k."'=>'".str_replace("'","\'",$v)."',".PHP_EOL;
	}
	$str.= ');';
	$str.= '?>';
	$fp = fopen(ROOT_DIR.'files/languages/'.$edit[0].'/'.$edit[1].'/'.$edit[2].'.php', 'w');
	fwrite($fp,$str);
	fclose($fp);
}
//конкретный модуль
else {
	$data = array(
		'id'=>$edit[3],
		$edit[2]=>htmlspecialchars_decode($post['content'])
	);
	//исключения для языков
	/* *
	//перечисление зеркальных модулей, где используюся приставки к названию колонок (например name1 для name)
	if (in_array($edit[1],array('pages','shop_products'))) {
		$data[$edit[2].$edit[0]] = $data[$edit[2]];
		//обновляем оригинальное поле
		if ($edit[0]!=1) unset($data[$edit[2]])
	}
	/* */
	$lang_field = $edit[2].$edit[0];
        $sql_fields = mysql_select("SHOW COLUMNS FROM `{$edit[1]}`",'rows_field');
        if(isset($sql_fields[$lang_field])) $data[$lang_field] = $data[$edit[2]]; 
        if($edit[0] != 1) unset($data[$edit[2]]);
	mysql_fn('update',$edit[1],$data);
}

echo 1;

die();
