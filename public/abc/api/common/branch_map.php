<?php

//список филлиалов для карты

//определение значений формы
$fields = array(
	'id'		=>	'int',
);
//создание массива $post
$post = form_smart($fields,stripslashes_smart($_GET)); //print_r($post);

if ($post['id'] AND $branch = mysql_select("
	SELECT * FROM shop_branches
	WHERE id=".$post['id']." AND display=1
",'row')) {
	$api['content'] = $branch['name'];
}
else $api['content'] = 'error #branch';
