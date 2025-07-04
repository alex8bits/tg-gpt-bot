<?php

/*
 * v1.4.14 - event_func
 * v1.4.16 - $delete удалил confirm
 * v1.4.17 - сокращение параметров form
 * v1.4.20 - значение в поле
 * v1.4.45 - карты и многоуровневый словарь
 */

$locales = array(
    'en'	=>	'Английский',
    'ar'	=>	'Арабский',
    'bg'	=>	'Болгарский',
    'ca'	=>	'Каталанский',
    'cn'	=>	'Китайский',
    'cs'	=>	'Чешский',
    'da'	=>	'Датский',
    'de'	=>	'Немецкий',
    'el'	=>	'Греческий',
    'es'	=>	'Испанский',
    'eu'	=>	'Баскский',
    'fa'	=>	'Фарси',
    'fi'	=>	'Финский',
    'fr'	=>	'Французский',
    'he'	=>	'Иврит',
    'hu'	=>	'Венгерский',
    'it'	=>	'Итальянский',
    'ja'	=>	'Японский',
    'kk'	=>	'Казахский',
    'lt'	=>	'Литовский',
    'lv'	=>	'Латышский',
    'nl'	=>	'Голландский',
    'no'	=>	'Норвежский',
    'pl'	=>	'Польский',
    'ptbr'	=>	'Португальский (Бразилия)',
    'ptpt'	=>	'Португальский',
    'ro'	=>	'Румынский',
    'ru'	=>	'Русский',
    'si'	=>	'Словенский',
    'sk'	=>	'Словацкий',
    'sl'	=>	'Словенский',
    'sr'	=>	'Сербский',
    'th'	=>	'Таиландский',
    'tr'	=>	'Турецкий',
    'tw'	=>	'Тайванский',
    'ua'	=>	'Украинский',
    'vi'	=>	'Вьетнамский',
);

//удаление языка
function event_delete_languages ($q) {
    global $config;
    if ($q) {
        foreach ($config['lang_tables'] as $key => $val) {
            foreach ($val as $k => $v) {
                mysql_fn('query', "ALTER TABLE `" . $key . "` DROP `" . $k . $q['id'] . "`");
            }
        }
    }
}

function event_change_languages ($q) {
    global $config;
    if (is_dir(ROOT_DIR . 'files/languages/' . $q['id'] . '/dictionary') || mkdir(ROOT_DIR . 'files/languages/' . $q['id'] . '/dictionary', 0755, true)) {
        $post = stripslashes_smart($_POST);
        if (@$post['dictionary']) foreach ($post['dictionary'] as $key => $val) {
            $str = '<?php' . PHP_EOL;
            $str .= '$lang[\'' . $key . '\'] = array(' . PHP_EOL;
            foreach ($val as $k => $v) {
                //v1.4.45 - для многоуровневого словаря
                if (is_array($v)) $v = serialize($v);
                $str .= "	'" . $k . "'=>'" . str_replace("'", "\'", $v) . "'," . PHP_EOL;
            }
            $str .= ');';
            $str .= '?>';
            $fp = fopen(ROOT_DIR . 'files/languages/' . $q['id'] . '/dictionary/' . $key . '.php', 'w');
            fwrite($fp, $str);
            fclose($fp);
        }
    }
    //если мультиязычный то нужно добавлять колонки в мультиязычные таблицы
    if ($config['multilingual']) {
        if ($_GET['id'] == 'new') {
            foreach ($config['lang_tables'] as $key=>$val) {
                foreach ($val as $k=>$v) {
                    mysql_fn('query',"ALTER TABLE `".$key."` ADD `".$k.$q['id']."` ".$v." AFTER `".$k."`");
                }
            }
        }
    }
}

//многоязычный
if ($config['multilingual']) {
    $module['save_as'] = true;
    $table = array(
        'id'			=>	'rank:desc name id',
        'name'			=>	'',
        'rank'			=>	'',
        'url'			=>	'',
        'localization'	=>	$locales,
        'display'		=>	'display'
    );
    $form[0][] = array('input td4','name');
    $form[0][] = array('input td2','rank');
    $form[0][] = array('input td2','url');
    $form[0][] = array('select td2','localization',array('value'=>array(true,$locales)));
    $form[0][] = array('checkbox td2','display');
}
//одноязычный
else {
    $module['one_form'] = true;
    $get['id'] = 1;
    if ($get['u']!='edit') {
        $post = mysql_select("
			SELECT *
			FROM languages
			WHERE id = 1
			LIMIT 1
		",'row');
    }
}

$a18n['localization'] = 'localization';

//исключения
if ($get['u']=='edit') {
    unset($post['dictionary']);
}
else {
    if ($get['id']>0) {
        $root = ROOT_DIR . 'files/languages/' . $get['id'] . '/dictionary';
        if (is_dir($root) && $handle = opendir($root)) {
            while (false !== ($file = readdir($handle))) {
                if (strlen($file) > 2)
                    include(ROOT_DIR . 'files/languages/' . $get['id'] . '/dictionary/' . $file);
            }
        }
    }
}

//v1.4.16 - $delete удалил confirm
$delete = array('pages'=>'language');

//вкладки
$tabs = array(
    0 => 'Общее',
);

$form[0][] = lang_form('textarea td12','common|prompt','Промт');


//v1.4.45 - правки в карты
/* *
$form[8][] = array('yandex_map','dictionary[map]',array(
	'value'=>@$lang['map'],
));
html_sources('footer','yandex_map');
/* */
$form[8][] = array('google_map','dictionary[map]',array(
    'value'=>@$lang['map'],
    //api/common/google_autocomplete - с автозаполнением или без
    'autocomplete'=>0
));
html_sources('footer','google_map');
/* */


function lang_form($type,$key,$name) {
    global $lang;
    $key = explode('|',$key);
    //автозаполнение пустых полей
    if (/*@$_GET['fuel'] AND */!isset($lang[$key[0]][$key[1]])) $lang[$key[0]][$key[1]] = $name;
    return array ($type,'dictionary['.$key[0].']['.$key[1].']',array(
        'name'=>$name.' {'.$key[0].'|'.$key[1].'}',
        'title'=>$key[0].'|'.$key[1],
        //v1.4.20 - значение в поле
        'value'=>$lang[$key[0]][$key[1]]
    ));
}
