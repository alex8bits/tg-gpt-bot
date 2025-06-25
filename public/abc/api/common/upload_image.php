<?php

/*
 * скрипт для загрузки файлов через нтмл5
 * создает временную папку на севрере /files/temp/*
 * что позволяет делать предзагрузку файла на сервер до отправки формы
 */

//sleep(1);
require_once(ROOT_DIR . 'functions/common_func.php');
require_once(ROOT_DIR . 'functions/string_func.php');
require_once(ROOT_DIR . 'functions/file_func.php');	//функции для работы с файлами
require_once(ROOT_DIR . 'functions/image_func.php');

$error = '';

//todo - доработать проверку прав доступа и хранение картинок

//исключение для дисконекта, запрос делает нода и там без авторизации
if (!access('user auth')) $error = 'Вы не авторизированы';
//dd($user);
//если нет ошибок то отправляем сообщение
if (!$error) {
	$file = @$_FILES['file'];
	$pathinfo = pathinfo($file['name']);
	//очищаем имя файла
	$file['name'] = strtolower(trunslit($pathinfo['filename'])); //название файла
	//если имя файла пустое то делаем md5
	if ($file['name']=='') {
		$file['name'] = substr(md5($file['tmp_name']), 0, 10);
	}
	//полное название файла с расширением
	$file['name'].= '.'.strtolower($pathinfo['extension']);
	$path = 'files/images/'; //папка от корня основной папки
	$root = ROOT_DIR.$path;
	if (is_dir($root) || mkdir ($root,0755,true)) { //создание папок для файла
		copy($file['tmp_name'],$root.$file['name']);
		if (img_process ('resize',$root.$file['name'],'800x800')) {
			echo '/' . $path . $file['name'];
		}
	}
}


die();