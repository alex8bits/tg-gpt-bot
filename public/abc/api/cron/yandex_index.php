<?php

//скрипт проверяет наличие страницы в индексе яндекса

if($seo_page = mysql_select("
	SELECT *
	FROM seo_pages
	WHERE display=1
	ORDER BY yandex_check
	LIMIT 1
",'row')) {
	$url = 'http://xmlsearch.yandex.ru/xmlsearch?user='.$config['yandex_user'].'&key='.$config['yandex_key'].'&query='.$_SERVER['SERVER_NAME'].$seo_page['url'];
	if (@$result = file_get_contents($url)) {
		//echo '<textarea name="Name" rows=5 cols=20 wrap="off">'.$result.'</textarea>';
		if (strpos($result, "Искомая комбинация слов нигде не встречается")) $data['yandex_search'] = 0;
		elseif (strpos($result, "results")) $data['yandex_search'] = 1;
		else die('error 2');
		$data['id'] = $v['id'];
		$data['yandex_check'] = date('Y-m-d H:i:s');
		//print_r($data);
		mysql_fn('update',$k,$data);
		echo '1';
	}
	else echo 'error 3';
}
else echo 'error 1';

die();