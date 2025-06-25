<?php

//200 яндекс - проверка заказа

$password = $config['yandex_shopPassword'];
$post = $_POST;

//синхронизируем данные мерчанта с полями заказа
$order = array(
	'id'=>@$post['orderNumber'],
	'total'=>@$post['orderSumAmount'],
);

//Ошибка разбора запроса
if (!isset($post['md5']) OR !isset($post['orderNumber'])) {
	$code = 200;
}
else {
	$md5 = md5($post['action'].';'.$post['orderSumAmount'].';'.$post['orderSumCurrencyPaycash'].';'.$post['orderSumBankPaycash'].';'.$post['shopId'].';'.$post['invoiceId'].';'.$post['customerNumber'].';'.$password);
	//не совпадает мд5
	if (strtoupper($post['md5'])!=strtoupper($md5)) {
		$code = 1;
	}
	else {
		// проверка есть ли такой заказ в базе
		$check = check_order ($order);
		if ($check=='success') {
			$code = 0;
		}
		else {
			$code = 100;
			$log['error'] = $check;
		}
	}
}
$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml.= '<checkOrderResponse performedDatetime="'.$post['requestDatetime'].'" code="'.$code.'" invoiceId="'.$post['invoiceId'].'" shopId="'.$post['shopId'].'"/>';
header('Content-type: text/xml; charset=UTF-8');
echo $xml;