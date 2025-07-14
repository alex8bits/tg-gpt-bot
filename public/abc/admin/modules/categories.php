<?php

$table = array(
    'id' => '',
    'name' => 'name:asc'
);

$filter[] = array('search');
$where = "";
if (isset($get['search']) && $get['search'] != '') {
    $search = mysql_res(mb_strtolower($get['search'], 'UTF-8'));
    $where .= "
        AND (
            LOWER(categories.name) LIKE '%$search%'
        )
    ";
}
$query = "
	SELECT * FROM categories
	WHERE 1 " . $where;


$form[] = array('input td12', 'name');
