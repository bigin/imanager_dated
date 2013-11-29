<?php
function generate_jsonp($data) {
	if (preg_match('/\W/', $_GET['callback'])) {
		// if $_GET['callback'] contains a non-word character,
		// this could be an XSS attack.
		header('HTTP/1.1 400 Bad Request');
		exit();
	}

	$arr = array('item1'=>"I love jquery4u",'item2'=>"You love jQuery4u",'item3'=>"We love jQuery4u");
	header('Content-type: application/javascript; charset=utf-8');
	//echo json_encode($data);
	print sprintf('%s(%s);', $_GET['callback'], json_encode($data));
}
//echo json_encode();
generate_jsonp($_GET['cat']);

?>