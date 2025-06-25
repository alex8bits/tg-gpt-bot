<?php

//собирает все js в один бандл
// /api/bundle/common.js

header('Content-Type: application/javascript; charset=UTF-8');

include_once(ROOT_DIR . 'plugins/jquery/jquery-1.11.3.min.js');
include_once(ROOT_DIR . 'templates/scripts/common.js');

/*
Вызов в шаблоне
<script  type="text/javascript" src="/common.js.php?lang=<?=$lang['localization']?>"></script>
*/
$lang = trim(@$_GET['lang']);
if ($lang=='') $lang = 'ru';
include_once(ROOT_DIR . 'plugins/jquery/jquery-validation-1.8.1/localization/messages_'.$lang.'.js');

die();