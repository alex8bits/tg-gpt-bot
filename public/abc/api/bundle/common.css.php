<?php

//собирает все css в один бандл
// /api/bundle/common.css

header('Content-type: text/css; charset=UTF-8');

include_once(ROOT_DIR . 'templates/css/common.css');

die();