<?php

//обратная связь

$customers = mysql_select("SELECT id, name, telegram_id FROM customers ORDER BY name", 'array');
$a18n['status'] = 'статус';

$statuses = [
    'новая' => 'новая',
    'в обработке' => 'в обработке',
    'закрыта' => 'закрыта',
];

$table = array(
    'id' => 'id:desc',
    'customer_id' => '',
    'status' => '',
    'text' => '',
    'created_at' => 'date'
);

$filter[] = array('search');
$filter[] = array('status',$statuses,NULL,true);
$filter[] = array('date_from');
$filter[] = array('date_to');

$where = '';

if (isset($get['search']) && $get['search']!='') $where.= "
	AND (
		LOWER(feedback.text) like '%".mysql_res(mb_strtolower($get['search'],'UTF-8'))."%'
	)
";
if (isset($get['status']) && $get['status']!='') $where.= "
	AND (
		LOWER(feedback.status) like '%".mysql_res(mb_strtolower($get['status'],'UTF-8'))."%'
	)
";
if (@$_GET['date_from']) {
    $where.= " AND created_at>='".mysql_res($_GET['date_from'])."'";
}
if (@$_GET['date_to']) {
    $where.= " AND created_at<='".mysql_res($_GET['date_to'])."'";
}

$query = "
	SELECT * FROM feedback
	WHERE 1 " . $where;

$form[] = array('select td6', 'customer_id', array(
    'value' => array(true, $customers)
));
$form[] = array('select td6', 'status', array(
    'value' => array(true, $statuses)
));
$form[] = array('textarea td12', 'text');
