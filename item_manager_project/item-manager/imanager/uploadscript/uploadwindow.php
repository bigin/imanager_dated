<?php

// Upload script v. 1.5
// Created by Mongo Juni, 2011
// www.ehret-studio.de

$spath = dirname($_SERVER['SCRIPT_FILENAME']);
require_once('upload.ini.php');
require_once($spath.'/model/upload.class.php');
require_once($spath.'/model/image.filter.class.php');
require_once($spath.'/model/generator.php');

// Define some variables
$_lang = array();
$upload = null;
$filter = null;
$err = '';
$nameBuff = '';

// Include language file
include ($spath.'/lang/english.inc.php');
if($par_systeminfo['language'] != 'english')
    if(file_exists($spath.'/lang/'.$par_systeminfo['language'].'.inc.php'))
	    include ($spath.'/lang/'.$par_systeminfo['language'].'.inc.php');


prepDataWinDisplay($par_template, $par_systeminfo);

$upload = uploadMain::getInstance($_lang, $par_systeminfo, $par_template, $par_imagefilter);

// Delete globals
unset($inputId);
unset($_lang);
unset($par_systeminfo);
unset($par_template);
unset($par_imagefilter);

$filter = new filter();

// Upload image  -Event
if(!empty($_POST['submit'])) {
    // Destroy delImageId
    $_GET['delImageId'] = '';
    // Check return parameter
    if(true !== $filter->fileFilter())
	$upload->setPropValue(TEMPLATE_PROP, 'lang_info_display', 
	                      $upload->getPropValue(ERROR_PROP, 'value'), 'windowcontent');
}
// Delete image  -Event
if(isset($_GET['inputName']) && !empty($_GET['delImageId'])) {
    if(true != genImgRemove()) {
	$nameBuff = explode('_', $_GET['delImageId'], 3);
	$err = sprintf($upload->getPropValue(LANGUAGE_PROP, 'err_delete_file'), '`'.$nameBuff[2].'`');
	$upload->setPropValue(TEMPLATE_PROP, 'lang_info_display', $err, 'windowcontent');
    }
}

// ~~TEMPLATES PARSING SECTION~~

// Generate loop data
if(!genMultiFileDisplays()) {
    echo genMessage(0);
    exit();
}

// Generates javaScript body block
genJsInitsDisplay();

// Destroy unnecessary TV's
if($upload->getPropValue(TEMPLATE_PROP, 'js_counter', 'windowimageloop') <= 0)
    $upload->setPropValue(TEMPLATE_PROP, 'lang_select_image', '', 'windowcontent');

// Javascript block will be added to the heater template
if(true !== $upload->callParentMethod('registerDoc', 'windowjstpl', 
				$upload->getPropValue(SYSTEM_PROP, 'templatedir')
				.'/window.js.tpl')) {
    // Show your error message in a beautiful format
    echo genMessage(0);
    exit();
}
if(true !== $upload->callParentMethod('parseTV', 'windowjstpl',
				      $upload->getPropValue(TEMPLATE_PROP, 'windowjsheader'))) {
    echo genMessage(0);
    exit();
}

// Generates a window header template
if(true !== $upload->callParentMethod('registerDoc', 'header', 
				$upload->getPropValue(SYSTEM_PROP, 'templatedir')
				.'/window.header.tpl')) {
    echo genMessage(0);
    exit();
}
if(true !== $upload->callParentMethod('parseTV', 'header', 
					$upload->getPropValue(TEMPLATE_PROP, 'windowheader'))) {
    genMessage(0);
    exit();
}

// Generate JavaScript body block
if(true !== $upload->callParentMethod('registerDoc', 'jsbodyblock', 
				$upload->getPropValue(SYSTEM_PROP, 'templatedir')
				.'/window.js.body.tpl')) {
    echo genMessage(0);
    exit();
}
if(true !== $upload->callParentMethod('parseTV', 'jsbodyblock', 
					$upload->getPropValue(TEMPLATE_PROP, 'windowjsbody'))) {
    genMessage(0);
    exit();
}

// Generate content template
if(true !== $upload->callParentMethod('registerDoc', 'content', 
				$upload->getPropValue(SYSTEM_PROP, 'templatedir')
				.'/window.content.tpl')) {
    echo genMessage(0);
    exit();
}
if(true !== $upload->callParentMethod('parseTV', 'content', 
					$upload->getPropValue(TEMPLATE_PROP, 'windowcontent'))) {
    genMessage(0);
    exit();
}

// Generate body block
if(true !== $upload->callParentMethod('registerDoc', 'body', 
				$upload->getPropValue(SYSTEM_PROP, 'templatedir')
				.'/window.body.tpl')) {
    echo genMessage(0);
    exit();
}
if(true !== $upload->callParentMethod('parseTV', 'body', 
					$upload->getPropValue(TEMPLATE_PROP, 'windowbody'))) {
    genMessage(0);
    exit();
}

// Output
echo $upload->getPropValue(TEMPLATE_PROP, 'header', 'tpls');
echo $upload->getPropValue(TEMPLATE_PROP, 'body', 'tpls');
?>
