<?php

require_once(ROOT_DIR . 'functions/mail_func.php');	//функции почты

//определение значений формы
$fields = array(
	'product'		=> 'required int',
	'rating'		=> 'int',
	'email'			=> 'required email',
	'name'			=> 'required text',
	'text'			=> 'required text',
	'captcha'		=> 'required captcha2',
	'language'      => 'int',
);
//создание массива $post
$post = form_smart($fields,stripslashes_smart($_POST)); //print_r($post);

//основной язык
//$lang = lang($post['language']);

//сообщения с ошибкой заполнения
$message = form_validate($fields,$post);

$api['success'] = 0;
$api['message'] = '';

//если нет ошибок то отправляем сообщение
if (count($message)==0) {
	if ($product = mysql_select("SELECT sp.*,sc.url category_url FROM shop_products sp, shop_categories sc WHERE sp.category=sc.id AND sp.id=".$post['product'],'row')) {
		unset($post['captcha'],$post['language']);
		$post['date'] = date('Y-m-d H:i:s');
		$post['text'] = '<p>'.preg_replace("/\n/","<br />",$post['text']).'</p>';
		$post['id'] = mysql_fn('insert','shop_reviews',$post);
		$post['product'] = $product;
		mailer('shop_review',$lang['id'],$post);
		$api['success'] = 1;
		$api['data'] = array(
			//общее количество товара
			array(
				'selector' => '#shop_review_form',
				'method' => 'html',
				'content' => i18n('shop|review_is_sent')
			)
		);
		//перещет рейтинга товара - перенс в админку
		/*$data = array(
			'id' => $product['id'],
			'rating' => mysql_select("SELECT SUM(rating)/COUNT(id) FROM shop_reviews WHERE product=".$product['id'],'string'),
		);
 		mysql_fn('update','shop_products',$data);
		*/
	}
	else $api['message'] = 'error #insert';
}
else {
	$api['data'] = array(
		array(
			'selector' => '#shop_review_form',
			'method' => 'append',
			'content' => html_render('form/messages',$message)
		)
	);
}