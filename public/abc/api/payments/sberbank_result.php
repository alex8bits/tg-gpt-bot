<?php

//900 сбербанк - обработка оплаты

define('GATEWAY_URL', 'https://3dsec.sberbank.ru/payment/rest/');
$payment = 900;
$data2 = array(
	'userName' => $config['sberbank_userName'],
	'password' => $config['sberbank_password'],
	'orderId' => $_GET['orderId']
);
//
$response = gateway('getOrderStatusExtended.do', $data2);
if ($response['orderStatus']==2) {
	//синхронизируем данные мерчанта с полями заказа
	$order = array(
		'id' =>  $response['orderNumber'],
		'payment'=>$payment,
		'total' => floatval($response['amount']) / 100, // возвращает копейки
	);
	$check = check_order($order);
	if ($check == 'success') {
		$data['payment'] = $payment; //$config['payments']
//            echo 'OK';
		// после сохранения редирект на успешную оплату
		$redirectTo = get_url('basket','success');
	} elseif ($check == 'paid') {
		header('Location: ' . get_url('basket','success'));
	} else {
		echo $check;
		$log['error'] = $check;
	}
}
else {
	echo $response['ErrorCode'].'-'.$response['orderStatus']
		.'-'.$response['errorMessage'].'-orderNo:'.$response['orderNumber'];
	$log['error'] = $response['ErrorCode'].'-'.$response['OrderStatus'];
}