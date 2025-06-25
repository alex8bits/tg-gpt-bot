<?php

//400 альфабанк - создание заказа

if ($order = mysql_select("SELECT * FROM orders WHERE id=".intval(@$_POST['id']),'row')) {
	if (@$_POST['hash']==md5($order['id'].$order['date'])) {
		// если заказ ранее оплачен, просто редиректим на ОК
		if ($order['paid'] == 1) {
			header('Location: ' . get_url('basket', $order));
			die();
		}

		//todo - добавить урл для тестирования
		define('GATEWAY_URL', 'https://web.rbsuat.com/ab/rest/');
		$data2 = array(
			'userName' => @$config['alfabank_userName'],
			'password' => @$config['alfabank_password'],
			'orderNumber' => $order['id'],
			'amount' => $order['total'],
			'returnUrl' => $config['http_domain'].'/api/payments/alfabank_result/'
		);
		/**
		 * ЗАПРОС РЕГИСТРАЦИИ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
		 *      register.do
		 *
		 * ПАРАМЕТРЫ
		 *      userName        Логин магазина.
		 *      password        Пароль магазина.
		 *      orderNumber     Уникальный идентификатор заказа в магазине.
		 *      amount          Сумма заказа в копейках.
		 *      returnUrl       Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
		 *
		 * ОТВЕТ
		 *      В случае ошибки:
		 *          errorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
		 *          errorMessage    Описание ошибки.
		 *
		 *      В случае успешной регистрации:
		 *          orderId         Номер заказа в платежной системе. Уникален в пределах системы.
		 *          formUrl         URL платежной формы, на который надо перенаправить браузер клиента.
		 *
		 *  Код ошибки      Описание
		 *      0           Обработка запроса прошла без системных ошибок.
		 *      1           Заказ с таким номером уже зарегистрирован в системе.
		 *      3           Неизвестная (запрещенная) валюта.
		 *      4           Отсутствует обязательный параметр запроса.
		 *      5           Ошибка значения параметра запроса.
		 *      7           Системная ошибка.
		 */
		/*function gateway($method, $data)
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
		}*/
		$response = gateway('register.do', $data2);
		if (isset($response['errorCode'])) {
			// В случае ошибки обработка ниже
			$log = 'error '.$response['errorCode'];
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
log_add('alfabank_error_'.date('Y-m').'.txt',$log);
//перекидываем на ошибку
header('Location: ' . get_url('basket','fail'));
die();