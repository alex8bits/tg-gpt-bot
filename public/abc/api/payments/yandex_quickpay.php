<?php

//250 яндекс2 - быстрая оплата сразу на кошель v1.2.46
$secret_key = $config['yandex2_secret_key'];
$post = $_POST;
//log_add('yandex.txt',$post);
// $_POST['operation_id'] - номер операция
// $_POST['amount'] - количество денег, которые поступят на счет получателя
// $_POST['withdraw_amount'] - количество денег, которые будут списаны со счета покупателя
// $_POST['datetime'] - тут понятно, дата и время оплаты
// $_POST['sender'] - если оплата производится через Яндекс Деньги, то этот параметр содержит номер кошелька покупателя
// $_POST['label'] - лейбл, который мы указывали в форме оплаты
// $_POST['email'] - email покупателя (доступен только при использовании https://)

$sha1 = sha1( $_POST['notification_type'] . '&'. $_POST['operation_id']. '&' . $_POST['amount'] . '&643&' . $_POST['datetime'] . '&'. $_POST['sender'] . '&' . $_POST['codepro'] . '&' . $secret_key. '&' . $_POST['label'] );

if ($sha1 != $_POST['sha1_hash'] ) {
	// тут содержится код на случай, если верификация не пройдена
	$log['error'] = 'error';
}
else {
	$order = array(
		'id' => @intval($_POST['label']),
		'total' => @$_POST['withdraw_amount'],
	);
	//log_add('yandex.txt',$order);
	$check = check_order ($order);
	if ($check=='success') {
		$data['payment'] = 250; //$config['payments']
		if (@$post['paymentType'] == 'PC') $data['payment'] = 251; //yandex
		if (@$post['paymentType'] == 'AC') $data['payment'] = 252; //карта
		if (@$post['paymentType'] == 'WM') $data['payment'] = 253; //webmoney
		if (@$post['paymentType'] == 'QW') $data['payment'] = 254; //qiwi
	}
	else {
		echo $check;
		$log['error'] = $check;
	}
}