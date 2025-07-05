<?php

//Сообщения
$a18n['customer_id'] = 'Клиент';
$a18n['dialog_id'] = 'Номер диалога';
$a18n['gpt_bot_id'] = 'Номер бота';
$a18n['content'] = 'Текст';
$a18n['role'] = 'Роль';

$table = array(
    'id' => 'id:desc',
    'customer_id' => '',
    'dialog_id' => '',
    'gpt_bot_id' => '',
    'content' => '',
    'role' => '',
    'created_at' => 'date'
);


$where = "";
if (isset($get['customer_id']) && $get['customer_id'] != '') {
    $where .= "
        AND customer_id = " . $get['customer_id'] . "
    ";
}

$query = "
	SELECT * FROM messages
	WHERE 1 " . $where;

$filter[] = array('search');


$form[] = array('input td3', 'telegram_id');
$form[] = array('input td3', 'name');
$form[] = array('input td3', 'rating');
