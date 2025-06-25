<?php

//обратная связь
/*
 * v1.2.22 - добавил
 * v1.4.48 - удалил дату
 */

require_once(ROOT_DIR.'functions/mail_func.php');	//функции почты

//массив ответа
/*
$api = array(
	'success' => 1, //если успешно
	'message' => 'нтмл код сообщения для вставки в форму',
	'script' => '<script>alert("1")</script>'; //жаваскрипт код для выполнения
);
*/

//определение значений формы
$fields = array(
	'language'		=>	'required int',
	'email'			=>	'required email',
	'name'			=>	'required text',
	'text'			=>	'required text',
	'captcha'		=>	'required captcha2',
	'page_name'     =>  'text',
	'page_url'      =>  'text'
);
//создание массива $post
$post = form_smart($fields,stripslashes_smart($_POST)); //print_r($post);

//$lang['id'] = $post['language'];

//сообщения с ошибкой заполнения
$message = form_validate($fields,$post);

//если нет ошибок то отправляем сообщение
if (count($message)==0) {
	unset($post['captcha']);
	//прикрепленные файлы
	$files = array();
	$post['files'] = array();
	if (isset($_FILES['attaches']['name']) AND is_array($_FILES['attaches']['name'])) {
		foreach ($_FILES['attaches']['name'] as $k => $v) if ($v) {
			$name = trunslit($v);
			$files[$name] = $_FILES['attaches']['tmp_name'][$k];
			$post['files'][] = array(
				'name' => $v,
				'file' => $name
			);
		}
	}
	//запись сообщения в базу вместе с файлами
	$post['files'] = count($post['files']) ? serialize($post['files']) : '';
	//$post['date'] = date('Y-m-d H:i:s');
	if ($post['id'] = mysql_fn('insert', 'feedback', $post)) {
		if ($post['files']) {
			$i = 0;
			foreach ($files as $k => $v) {
				$path = ROOT_DIR . 'files/feedback/' . $post['id'] . '/files/' . $i . '/';
				mkdir($path, 0755, true);
				copy($v, $path . $k);
				$i++;
			}
		}
	}
	//если неудачный инсерт в базу то пишем лог ошибок
	else {
		log_add('feedback.txt',$post);
	}
	//6-й параметр кому ответить
	mailer('feedback', $lang['id'], $post, false, false, $post['email'], $files);
	$api['success'] = 1;
	$api['data'] = array(
		//добавляем комент в список коментов
		array(
			'selector' => '#feedback_success',
			'method' => 'modal',
		)
	);
}
else {
	//ответ с коментом для вставки в список коментов
	$api['data'] = array(
		//добавляем комент в список коментов
		array(
			'selector' => '#feedback .message_box',
			'method' => 'html',
			'content' => html_render('form/messages',$message)
		)
	);
}