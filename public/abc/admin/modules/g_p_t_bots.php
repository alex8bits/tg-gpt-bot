<?php

//Клиенты

$referrers = mysql_select("SELECT id, name, theme, prompt, type FROM g_p_t_bots ORDER BY id", 'array');
$a18n['theme'] = 'Тема';
$a18n['type'] = 'Тип';
$a18n['system_request'] = 'Системный запрос';

$table = array(
    'id' => 'id:desc',
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

$query = "
	SELECT * FROM g_p_t_bots
	WHERE 1 " . $where;

$filter[] = array('search');


$form[] = array('input td6', 'name');
$form[] = array('select td6', 'type', array(
    'value' => array(true, $types)
));
$form[] = array('input td12', 'theme');
$form[] = array('textarea td12', 'prompt');
$form[] = array('textarea td12', 'system_request');
