<?php

//Клиенты

$referrers = mysql_select("SELECT id, name, theme, prompt, type FROM g_p_t_bots ORDER BY id", 'array');
$categories = mysql_select("SELECT id, name FROM categories ORDER BY id", 'array');
$a18n['theme'] = 'Ключевые слова';
$a18n['type'] = 'Тип';
$a18n['system_request'] = 'Системный запрос';

$table = array(
    'id' => 'id:desc',
    'category_id' => $categories,
    'name' => '',
    'theme' => '',
    'type' => ''
);

$types = [
    'обычный' => 'обычный',
    'приветственный' => 'приветственный',
    'распределитель' => 'распределитель',
    'модератор' => 'модератор',
    'приём претензий' => 'приём претензий',
];


$where = "";
if (isset($get['search']) && $get['search'] != '') {
    $search = mysql_res(mb_strtolower($get['search'], 'UTF-8'));
    $where .= "
        AND (
            LOWER(g_p_t_bots.name) LIKE '%$search%' OR
            LOWER(g_p_t_bots.theme) LIKE '%$search%'
        )
    ";
}


$filter[] = array('category_id', $categories, NULL, true);

if (@$_GET['country']) $where.= " AND g_p_t_bots.category_id=".intval($_GET['category_id']);

$query = "
	SELECT * FROM g_p_t_bots
	WHERE 1 " . $where;


$form[] = array('input td4', 'name');
$form[] = array('select td4', 'type', array(
    'value' => array(true, $types)
));
$form[] = array('select td4', 'category_id', array(
    'value' => array(true, $categories)
));
$form[] = array('textarea td12', 'theme');
$form[] = array('textarea td12', 'prompt', array('attr' => 'style="height:300px"',));
$form[] = array('textarea td12', 'system_request');
