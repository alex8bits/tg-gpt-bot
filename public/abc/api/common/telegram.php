<?php

require_once (ROOT_DIR.'functions/push_func.php');

if (@$_GET['secret']!=$config['telegram_secret']) die();

$config['telegram_chat_id'] = '247341751'; //ид пользователя для функции telegram_log

//установка вебхука
/* *
$url = 'https://demo.abc-cms.com/api/telegram?secret='.$config['telegram_secret'];
$result = telegram_setWebhook ($url);
if ($result) {
	echo telegram_push('getWebhookInfo');
}
/* */

$result = @file_get_contents('php://input');
//$result = 'test';
$data = json_decode($result, true);
$result = json_encode($data,JSON_UNESCAPED_UNICODE);
//telegram_log($result); //die();

if ($result) {
	//telegram_log('1');
	//callback_query - если использовали кнопку в инлайновой клавиатуре
	if (@$data['callback_query']) {
		//ид сообщения, в котором нажали на кнопку
		$message_id = $data['callback_query']['message']['message_id'];
		$chat = $data['callback_query']['message']['chat'];
		if (@$data['callback_query']['data']) {
			$result = telegram_action($data['callback_query']['data'],$chat,$message_id);
		}
	}
	//простое сообщение
	else {
		$chat = $data['message']['from'];
		$result = telegram_action($data['message']['text'],$chat);
	}
}

/**
 * @param $type - команда для действий бота
 * @param $chat - массив с данными пользователя
 * @param $message_id - ид сообщения, в котором нажали кнопку
 */
function telegram_action($type,$chat,$message_id=false) {
	$types = explode(' ',$type);
	if ($types[0] == '/start') {
		//тут параметр, который может передаваться при старте бота ?start=blabla
		if (@$types[1]) {

		}
	}
	elseif ($type == '/get_chat_id') {
		telegram_send_text ($chat['id'],$chat);
	}

}