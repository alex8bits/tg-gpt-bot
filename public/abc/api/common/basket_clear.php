<?php

//удаление всех товаров в корзине

$_SESSION['basket'] = array();

$api = array(
	'done'	=>	true,
	'total'	=>	0,
	'count'	=>	0,
);