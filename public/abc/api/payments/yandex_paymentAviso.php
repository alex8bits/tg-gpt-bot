<?php

//200 яндекс - оплата заказа

//строка лога
$log['merchant'] = 'yandex';

//подготавливаем данные
$password = $config['yandex_shopPassword'];
$post = $_POST;

//синхронизируем данные мерчанта с полями заказа
$order = array(
	'id' => @$post['orderNumber'],
	'total' => @$post['orderSumAmount'],
);

//Ошибка разбора запроса
if (!isset($post['md5']) OR !isset($post['orderNumber'])) {
	$code = 200;
	$log['error'] = 'invalid_request';
}
else {
	$md5 = md5($post['action'].';'.$post['orderSumAmount'].';'.$post['orderSumCurrencyPaycash'].';'.$post['orderSumBankPaycash'].';'.$post['shopId'].';'.$post['invoiceId'].';'.$post['customerNumber'].';'.$password);
	if (strtoupper($post['md5'])!=strtoupper($md5)) {
		$code = 1;
		$log['error'] = 'invalid_md5';
	}
	//не совпадает мд5
	else {
		$check = check_order ($order);
		if ($check=='success') {
			$code = 0;
			$data['payment'] = 200; //$config['payments']
			if ($post['paymentType'] == 'PC') $data['payment'] = 201; //yandex
			if ($post['paymentType'] == 'AC') $data['payment'] = 202; //карта
			if ($post['paymentType'] == 'WM') $data['payment'] = 203; //webmoney
			if ($post['paymentType'] == 'QW') $data['payment'] = 204; //qiwi
		}
		else {
			echo $check;
			$log['error'] = $check;
		}
	}
}
$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml.= '<paymentAvisoResponse performedDatetime="'.$post['requestDatetime'].'" code="'.$code.'" invoiceId="'.$post['invoiceId'].'" shopId="'.$post['shopId'].'"/>';
header('Content-type: text/xml; charset=UTF-8');
echo $xml;