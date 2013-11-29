<?php

function get_path_info()
{
		//return dirname(__FILE__);
}

function generate_json() {
	global $SITENAME, $SITEURL, $TEMPLATE, $TIMEZONE, $LANG, $SALT, $i18n, $USR, $PERMALINK, $GSADMIN, $components;
/*	ob_start();
print_r($_GET);

$logfile  = fopen("/var/www/projects/php/logfile.txt","a+");
		rewind($logfile);
		fwrite($logfile, ob_get_clean());
		fclose($logfile);*/

	include('/var/www/projects/php/edasada/index.php');
	// ?id=imanager&settings


	echo 'test';
	//$data = $_GET['cat'];
	//$arr = array('item1'=>"I love jquery4u",'item2'=>"You love jQuery4u",'item3'=>"We love jQuery4u");
	//header('Content-type: application/javascript; charset=utf-8');
	//echo 'test';
	//print sprintf('%s(%s);', $_GET['callback'], json_encode($data));
}

// model
#include(GSPLUGINPATH.'imanager/class/im.model.class.php');
// controller
#include(GSPLUGINPATH.'imanager/class/im.controller.class.php');
// category
#include(GSPLUGINPATH.'imanager/class/im.category.class.php');
// configurator
#include(GSPLUGINPATH.'imanager/class/im.fields.configurator.class.php');
// output
#include(GSPLUGINPATH.'imanager/class/im.output.class.php');
// reporter
#include(GSPLUGINPATH.'imanager/class/im.msg.reporter.class.php');

//$manager = new ImController();
//if(isset($_GET['cat']))
generate_json();

?>