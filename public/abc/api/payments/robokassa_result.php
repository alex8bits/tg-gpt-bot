<?php

//100 робокасса

//строка лога
$log['merchant'] = 'robokassa';

//собираем параметры
$shp_item = @$_REQUEST["Shp_item"];
$crc = @$_REQUEST["SignatureValue"];

////синхронизируем данные мерчанта с полями заказа
$order = array(
	'id' =>  @$_REQUEST["InvId"],
	'total' => $_REQUEST["OutSum"]
);

//подписи
$crc = strtoupper($crc);
$my_crc = strtoupper(md5($order['total'].':'.$order['id'].':'.$config['robokassa_password2'].':Shp_item='.$shp_item));
// проверка корректности подписи - check signature
if ($my_crc != $crc) {
	echo 'ERROR';
	$log['error'] = 'invalid_crc';
}
else {
	// признак успешно проведенной операции
	// success
	echo $order['id'];
	// проверка есть ли такой заказ в базе
	$check = check_order ($order);
	if ($check=='success') {
		echo ' OK';
		$data['payment'] = 100; //$config['payments']'
	}
	else {
		echo $check;
		$log['error'] = $check;
	}
}