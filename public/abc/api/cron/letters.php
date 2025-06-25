<?php

require_once(ROOT_DIR.'functions/mail_func.php');	//функции почты

//отправка писем из БД

$date =  date('Y-m-d H:i:s');

$where = @$_GET['id']>0 ? " AND id=".intval($_GET['id']):" AND date_sent=0";

$query = "SELECT * FROM letters WHERE 1 $where ORDER BY id LIMIT 50";
//echo $query;
$i = 0;
$ii = 0;
if ($letters = mysql_select($query,'rows')) {
	foreach ($letters as $q) {
		$ii++;
		//закрываем соединение с БД для отправки письма
		if ($config['mysql_connect']) mysql_close_db();
		//успешная отправка письма
		$email = array(
			'from' => array($q['sender']=>$q['sender_name']),
			'to' => $q['receiver'],
			'subject' => $q['subject'],
			'text' => $q['text']
		);
		if (email($email)) {
			$i++;
			mysql_fn('update','letters',array('date_sent'=>$date,'id'=>$q['id']));
		}
		else {
			log_add('mail.txt',$email);
		}
	}
}
$affected_rows = mysql_fn('query',"DELETE FROM letters WHERE date_sent!=0 AND (date_sent + interval 3 day)<'".$date."' ORDER BY id LIMIT 50",'affected_rows');
echo 'Отправлено '.$i.' из '.$ii;
echo '<br />Удалено '.$affected_rows;
die();