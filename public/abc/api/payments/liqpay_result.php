<?php

//500 liqpay (privat24) v.1.1.2
//строка лога
$log['merchant'] = 'liqpay';

//собираем параметры
$post = array(
	'data' => (string)@$_POST['data'],
	'signature'	=> (string)@$_POST['signature'],
);
$json = base64_decode($post['data']);
$data2 = json_decode($json,true);
$sign = base64_encode( sha1(
	$config['liqpay_private_key'] .
	$post['data'] .
	$config['liqpay_private_key']
	, 1 ));

//синхронизируем данные мерчанта с полями заказа
$order = array(
	'id' =>  @$data2['order_id'],
	'total' => @$data2['amount']
);

// проверка корректности подписи - check signature
if ($sign!=$post['signature']) {
	$log['error'] = 'invalid_signature';
}
else {
	// признак успешно проведенной операции
	if ($data2['status']=='success' OR $data2['status']=='wait_accept') {
		echo $order['id'];
		// проверка есть ли такой заказ в базе
		$check = check_order($order);
		if ($check == 'success') {
			$data['payment'] = 500; //$config['payments']'
		} else {
			$log['error'] = $check;
		}
	}
	else {
		$log['error'] = $data2['status'];
	}
	/*
	 * еще вот есть статусы с которыми не понятно что делать, они ни о чем и не понятно как с ними поступать
	processing    Платеж обрабатывается
	prepared    Платеж создан, ожидается его завершение отправителем
	wait_bitcoin    Ожидается перевод bitcoin от клиента
	wait_secure    Платеж на проверке
	wait_accept    Деньги с клиента списаны, но магазин еще не прошел проверку
	wait_lc    Аккредитив. Деньги с клиента списаны, ожидается подтверждение доставки товара
	hold_wait    Сумма успешно заблокирована на счету отправителя
	cash_wait    Ожидается оплата наличными в ТСО.
	wait_qr    Ожидается сканировани QR-кода клиентом.
	wait_sender    Ожидается подтверждение оплаты клиентом в приложении Privat24/Sender.
	wait_card    Не установлен способ возмещения у получателя
	wait_compensation    Платеж успешный, будет зачислен в ежесуточной проводке
	invoice_wait    Инвойс создан успешно, ожидается оплата
	wait_reserve    Средства по платежу зарезервированы для проведения возврата по ранее поданной заявке
	 */
}
