<?php

//заккоментировать в боевом режиме
$config['debug'] = true;

$action = @$_GET['payment_action']; //тип действия
$order = false; //массив заказа переданного от мерчанта
$data = false; //массив для обновления заказов
$date = date('Y-m-d H:i:s');
//массив данных для лога
$log = array(
	'date'=>$date,
	'ip'=>get_ip(),
);

$action = $u[3];
$file = ROOT_DIR.'api/payments/'.$u[3].'.php';
if ($u[3] AND file_exists($file)) {
	include_once($file);
}
//неизвестный мерчант (ошибка)
else {
	$log['merchant'] = 'unknown';
}

//записываем в лог ИД и стоимость заказа
//todo правильно бы добавить еще и валюту
if ($order) {
	$log['id'] = $order['id'];
	$log['total'] = $order['total'];
}

//если успешно то проводим оплату заказа
if ($data AND $order) {
	$data['id'] = $order['id'];
	$data['paid']= 1;
	$data['date_paid'] = $date;
	mysql_fn('update', 'orders', $data);
	//todo - доработать с языками
	$lang = lang();
	if ($order = mysql_select("
		SELECT o.*,ot.name".$lang['i']." ot_name,ot.text".$lang['i']." ot_text
		FROM orders o
		LEFT JOIN order_types ot ON ot.id=o.type
		WHERE o.id='".$order['id']."'
		LIMIT 1
	",'row')) {
		mailer('basket',1,$order,$order['email']);
		mailer('basket',1,$order);
		if (isset($redirectTo)) {
			header('Location: '. $redirectTo); exit();
		}
	}
	log_add('payment_success_'.date('Y-m').'.txt',$log);
}
//ошибка
else {
	log_add('payment_error_'.date('Y-m').'.txt',$log);
}

//функция проверки заказа
//todo - валюты заказа
function check_order ($data) {
	if ($order = mysql_select("SELECT * FROM orders WHERE id=".intval($data['id']),'row')) {
		//заказ уже оплачен
		if ($order['paid']>0) {
			return 'paid'; //оплачен
		}
		//не совпадает сумма оплаты (у альфабанка она не передается)
		elseif ($order['total']!=$data['total'] AND @$data['payment']!=400) {
			return 'total';
		}
		//все успешно
		else {
			return 'success';
		}
	}
	//нет такого заказа
	else return 'error';
}


function gateway($method, $data)
{
	$curl = curl_init(); // Инициализируем запрос
	curl_setopt_array($curl, array(
		CURLOPT_URL => GATEWAY_URL . $method, // Полный адрес метода
		CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
		CURLOPT_POST => true, // Метод POST
		CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
	));
	$response = curl_exec($curl); // Выполненяем запрос

	$response = json_decode($response, true); // Декодируем из JSON в массив
	curl_close($curl); // Закрываем соединение
	return $response; // Возвращаем ответ
}

die();