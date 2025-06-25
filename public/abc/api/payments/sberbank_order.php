<?php

//900 сбербанк - создание заказа

define('GATEWAY_URL', 'https://3dsec.sberbank.ru/payment/rest/');
//
if ($order = mysql_select("SELECT * FROM orders WHERE id=".intval(@$_POST['id']),'row')) {
	if (@$_POST['hash']==md5($order['id'].$order['date'])) {
		// если заказ ранее оплачен, просто редиректим на ОК
		if ($order['paid'] == 1) {
			header('Location: ' . get_url('basket', $order));
			die();
		}

		$data2 = array(
			'userName' => @$config['sberbank_userName'],
			'password' => @$config['sberbank_password'],
			'orderNumber' => $order['id'],
			'amount' => floatval($order['total']) * 100, // сумма должна быть в копейках
			'returnUrl' => $config['http_domain'].'/api/payments/sberbank_result/'
		);
		//
		$response = gateway('register.do', $data2);
		if (isset($response['errorCode'])) {
			// В случае ошибки обработка ниже
			$log = 'error '.$response['errorCode'] . ' ' . $response['errorMessage'];
		}
		// В случае успеха перенаправить пользователя на плетжную форму
		else {
			//тут возможно нужно будет сохранять урл в заказе на который перекидывать
			header('Location: ' . $response['formUrl']);
			die();
		}
	}
	else $log = 'error hash';
}
else $log = 'order not exists';
log_add('sberbank_error_'.date('Y-m').'.txt',$log);
//перекидываем на ошибку
header('Location: ' . get_url('basket','fail'));
die();