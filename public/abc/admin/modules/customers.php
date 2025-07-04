<?php

//Клиенты
$a18n['name'] = 'Имя';

$table = array(
    'id' => 'id:desc',
    'telegram_id' => '',
    'name' => '',
    'rating' => '',
    //'messages' => '<a href="/admin.php?m=messages&customer_id={id}">Сообщения</a>'
);


$where = "";
if (isset($get['search']) && $get['search'] != '') {
    $search = mysql_res(mb_strtolower($get['search'], 'UTF-8'));
    $where .= "
        AND (
            LOWER(customers.telegram_id) LIKE '%$search%' OR
            LOWER(customers.name) LIKE '%$search%'
        )
    ";
}

$query = "
	SELECT * FROM customers
	WHERE 1 " . $where;

$filter[] = array('search');


$form[] = array('input td3', 'telegram_id');
$form[] = array('input td3', 'name');
$form[] = array('input td3', 'rating');
