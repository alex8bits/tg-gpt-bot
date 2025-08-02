<?php

//Основные боты
$starting_bots = mysql_select("SELECT id, name FROM g_p_t_bots ORDER BY name", 'array');

$table = array(
    'id' => 'rank id:desc',
    'name' => '',
    'system_name' => '',
    'starting_bot' => '',
    'prompt' => '',
    'rank' => ''
);

$filter[] = array('search');
$where = "";
if (isset($get['search']) && $get['search'] != '') {
    $search = mysql_res(mb_strtolower($get['search'], 'UTF-8'));
    $where .= "
        AND (
            LOWER(main_bots.name) LIKE '%$search%'
        )
    ";
}


$query = "
	SELECT * FROM main_bots
	WHERE 1 " . $where;


$form[] = array('input td4', 'name');
$form[] = array('input td4', 'system_name');
$form[] = array('select td6', 'starting_bot', array(
    'value' => array(true, $starting_bots)
));
$form[] = array('textarea td12', 'prompt', array('attr' => 'style="height:300px!important"'));
