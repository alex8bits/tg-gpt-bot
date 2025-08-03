<?php

//GPT bots

$main_bots = mysql_select("SELECT id, name FROM main_bots ORDER BY id", 'array');
$categories = mysql_select("SELECT id, name FROM categories ORDER BY id", 'array');
$a18n['theme'] = 'Ключевые слова';
$a18n['type'] = 'Тип';
$a18n['system_request'] = 'Системный запрос';
$a18n['category_id'] = 'Категория';

$table = array(
    'id' => 'rank id:desc',
    'category_id' => $categories,
    'name' => '',
    'theme' => '',
    'type' => '',
    'rank' => ''
);

$types = [
    'обычный' => 'обычный',
    'приветственный' => 'приветственный',
    'распределитель' => 'распределитель',
    'модератор' => 'модератор',
    'приём претензий' => 'приём претензий',
];

$filter[] = array('search');
$filter[] = array('category_id', $categories, "Выберите категорию");
$filter[] = array('main_bots', $main_bots, 'Выберите основного бота');
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
/*if (isset($get['main_bots']) && $get['main_bots'] != '') {
    $where .= "
        AND (
            LOWER(g_p_t_bots.main_bots) LIKE %{$_GET['main_bots']}%
        )
    ";
}*/

if (@$_GET['category_id']) $where.= " AND g_p_t_bots.category_id=".intval($_GET['category_id']);
if (@$_GET['main_bots']) $where.= " AND g_p_t_bots.main_bots LIKE '%{$_GET['main_bots']}%' ";

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
$form[] = array('multicheckbox td3','main_bots',array(
    'value'=>array(true,'SELECT id,name FROM main_bots ORDER BY name'))
);
$form[] = array('textarea td12', 'theme');
$form[] = array('textarea td12', 'prompt', array('attr' => 'style="height:300px!important"'));
$form[] = array('textarea td12', 'system_request');
