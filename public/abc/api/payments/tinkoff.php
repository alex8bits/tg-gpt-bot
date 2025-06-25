<?php

//800 tinkoff - v1.2.54

/*
	 * https://oplata.tinkoff.ru/landing/develop/plug/http
	 *
	 * $_POST данные
	 * TerminalKey	String	Идентификатор магазина
	 * OrderId	String	Номер заказа в системе Продавца
	 * Success	Boolean	Успешность операции
	 * Status	String	Статус платежа (см. описание статусов операций)
	 * PaymentId	Number	Уникальный идентификатор платежа
	 * ErrorCode	String	Код ошибки, если произошла ошибка.
	 * Amount	Number	Текущая сумма транзакции в копейках
	 * RebillId	Number	Идентификатор рекуррентного платежа
	 * CardId	Number	Идентификатор привязанной карты
	 * Pan	String	Маскированный номер карты
	 * DATA	String	Дополнительные параметры платежа, переданные при создании заказа
	 * Token	String	Подпись запроса. Алгоритм формирования подписи описан в разделе "Проверка токенов"
	 * ExpDate	String	Срок действия карты
	 *
	 * Статусы платежей, по которым приходят http(s)-нотификации:
	 * Status	Описание
	 * AUTHORIZED	Деньги захолдированы на карте клиента. Ожидается подтверждение операции*
	 * CONFIRMED	Операция подтверждена
	 * REVERSED	Операция отменена
	 * REFUNDED	Произведён возврат
	 * PARTIAL_REFUNDED	Произведён частичный возврат
	 * REJECTED	Списание денежных средств закончилась ошибкой
	 */
$post = $_POST;
//log_add('tinkoff.txt',$post);
$array=array(
	'Amount','CardId','ErrorCode','ExpDate','OrderId','Pan',
	'Password','PaymentId','RebillId','Status','Success','TerminalKey'
);
$post['Password'] = $config['tinkoff_password'];
$post['TerminalKey'] = $config['tinkoff_terminalkey'];
$string = '';
foreach ($array as $k=>$v) {
	$string.=$post[$v];
}
$hash = hash('sha256',$string);

if ($hash != $_POST['Token'] ) {
	// тут содержится код на случай, если верификация не пройдена
	$log['error'] = 'error';
}
else {
	$order = array(
		'id' => @intval($_POST['OrderId']),
		'total' => @$_POST['Amount'],
	);
	//сумма в копейках
	$order['total'] = $order['total']/100;
	if(@$_POST['Status']=='CONFIRMED') {
		//log_add('tinkoff.txt',$order);
		$check = check_order($order);
		if ($check == 'success') {
			$data['payment'] = 800; //$config['payments']
			echo 'OK';
		}
		else {
			echo $check;
			$log['error'] = $check;
		}
	}
	else {
		$log['error'] = @$_POST['Status'];
	}
}