<?php

//sleep(2);

//добавление товара в корзину

//todo - добавить язык $lang

//определение значений формы
$fields = array(
	'id'		=>	'int',
	'count'			=>	'int',
);
//создание массива $post
$post = form_smart($fields,stripslashes_smart($_GET)); //print_r($post);

//id товара
if ($post['id']) {
	//количество
	if ($post['count']) {
		if ($q = mysql_select("
			SELECT *
			FROM shop_products
			WHERE id = '".$post['id']."'
		",'row')) {
			//массив товара для хранения в сессии
			$p = array(
				'id'	=> $q['id'],
				'name'	=> $q['name'],
				//'url'	=> $q['url'],
				'price'	=> $q['price'],
				'count'	=> $post['count']
			);
			//если товар уже есть в корзине
			$is = 0;
			if (isset($_SESSION['basket']['products']) && is_array($_SESSION['basket']['products'])) {
				foreach ($_SESSION['basket']['products'] as $k=>$v) {
					if ($v['id']==$p['id']) {
						$is = 1;
						$_SESSION['basket']['products'][$k]['count']+= $p['count'];
						break;
					}
				}
			}
			//если товара нет, то добавляем новый элемент в массив товаров
			if ($is==0) $_SESSION['basket']['products'][] = $p;
			//прибавляем количество и стоимость
			@$_SESSION['basket']['total']+= $p['price'] * $post['count'];
			@$_SESSION['basket']['count']+= $post['count'];

			//$api = $_SESSION['basket'];
			$api['data'] = array(
				//общее количество товара
				array(
					'selector' => '#basket_info .count',
					'method' => 'text',
					'content' => $_SESSION['basket']['count']
				),
				//общая сумма товаров
				array(
					'selector' => '#basket_info .total',
					'method' => 'text',
					'content' => price_format($_SESSION['basket']['total'])
				),
				//показать количество и сумму
				array(
					'selector' => '#basket_info .full',
					'method' => 'show',
				),
				//спрятать пустую корзину
				array(
					'selector' => '#basket_info .empty',
					'method' => 'hide',
				),
				//показать окно что товар добавлен
				array(
					'selector' => '#basket_message',
					'method' => 'modal',
				)
			);
		}
		else {
			$api['error_text'] = 'Такого товара не существует!';
		}
	}
	else {
		$api['error_text'] = 'Не указано количество товара!';
	}
}
else {
	$api['error_text'] = 'Не указан товар!';
}