<?php
// Upload script v. 1.5
// Created by Mongo Juni, 2011
// Modified by Bigin October, 2013 

// Include script settings
require_once('upload.ini.php');
// Include classes
require_once($par_systeminfo['classesdir'].'/upload.class.php');
// Include generator functions
require_once($par_systeminfo['classesdir'].'/generator.php');

// Define language array
$_lang = array();

// Include language file
include ($par_systeminfo['languagedir'].'/english.inc.php');
if($par_systeminfo['language'] != 'english')
    if (file_exists($par_systeminfo['languagedir'].'/'.$par_systeminfo['language'].'.inc.php'))
	    include ($par_systeminfo['languagedir'].'/'.$par_systeminfo['language'].'.inc.php');

// Check access `tmp` dir
if (false == is_writable ($par_systeminfo['script_root'].'/'.$par_systeminfo['tmpurl'])) {
    echo $_lang['err_nowrite'];
    exit();
}

/* Okay. We need to set some default values before the create instance action is called,
   In this way we can to keep our ini-file free of confusing clutter by preventing empty values. */
prepDataFormDisplay($par_template, $par_systeminfo);

$upload = uploadMain::getInstance($_lang, $par_systeminfo, $par_template, $par_imagefilter);

// We are going to delete global variables that we no longer need 
unset($inputId);
unset($_lang);
unset($par_systeminfo);
unset($par_template); 
unset($par_imagefilter);
?>
