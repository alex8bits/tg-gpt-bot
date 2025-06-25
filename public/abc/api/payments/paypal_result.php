<?php

//600 paypal

//строка лога
$log['merchant'] = 'paypal';

//обрабатываем данные
$post = $_POST;
//смотрим все что запрашивает пейлпал
log_add('paypal.txt',serialize($post),true);
$postdata = '';
//обратный запрос на пейпал для проверки заказа
foreach ($_POST as $key=>$value) $postdata.= $key.'='.urlencode($value).'&';
$postdata .= "cmd=_notify-validate";
$curl = curl_init("https://www.paypal.com/cgi-bin/webscr");
curl_setopt ($curl, CURLOPT_HEADER, 0);
curl_setopt ($curl, CURLOPT_POST, 1);
curl_setopt ($curl, CURLOPT_POSTFIELDS, $postdata);
curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
//нужно чтобы небыло ошибки You don't have permission to access "http://www.paypal.com/cgi-bin/webscr" on this server.
curl_setopt ($curl, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name'));
//расскоментировать на боевом, на локале ругается на эту строку
//curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 1);
$response = curl_exec ($curl);
//$response = "VERIFIED"; $post['item_number'] = 1;
curl_close ($curl);
//фиксируем ответ от пейпала
log_add('paypal.txt',$response,true);

//синхронизируем данные мерчанта с полями заказа
$order = array(
	'id'=>@$post['item_number'],
	'total'=>@$post['payment_gross'],
);

//успешный платеж
if ($response == "VERIFIED"){
	if (mb_strtolower($config['paypal_email'],'UTF-8')==mb_strtolower($post['receiver_email'],'UTF-8')) {
		$check = check_order ($order);
		if ($check=='success') {
			$code = 0;
			$data['payment'] = 600; //$config['payments']
		}
		else {
			echo $check;
			$log['error'] = $check;
		}
	}
	else {
		$log['error'] = 'invalid email';
	}
}
else {
	$log['error'] = 'not VERIFIED';
}