<?php

/**
* Plugin Name: Item Manager Extended
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/

# get correct id for plugin
$thisfile = basename(__FILE__, '.php');

# path & file constants definitions
define('ITEMDATA', GSDATAPATH.'imanager/');
define('ITEMUPLOADDIR', 'data/uploads/imanager/');
define('ITEMUPLOADPATH', GSDATAPATH.'uploads/imanager/');
define('ITEMDATAFILE', GSDATAOTHERPATH.'imanager.xml');
define('IM_CUSTOMFIELDS_FILE', 'im.fields.xml');
define('IMCSSPATH', 'imanager/css/');


// Initialize manager
$ititle = 'Item';
if(file_exists(ITEMDATAFILE))
{
    $im_datafile = getXML(ITEMDATAFILE);
    $ititle = (!empty($im_datafile->item->title) 
               ? $im_datafile->item->title : 'Item');
}
define('IMTITLE', $ititle);

// register plugin
register_plugin(
  $thisfile,
  'Item Manager Extended',
  '0.5',
  'Bigin 07.03.2013 (modified plugin of PyC)',
  'http://www.ehret-studio.de',
  'Full featured Item Manager',
  'imanager',
  'imanager'
);
// activate actions
add_action('nav-tab', 'createNavTab', array('imanager', $thisfile, IMTITLE.' Manager', 'view')); 
/* include your own CSS for beautiful manager style */
register_style('imstyle', $SITEURL.'plugins/'.$thisfile.'/css/im-styles.css', GSVERSION, 'screen');
queue_style('imstyle', GSBOTH);

// model class
include(GSPLUGINPATH.'imanager/class/im.model.class.php');
// controller class
include(GSPLUGINPATH.'imanager/class/im.controller.class.php');
// category model class
include(GSPLUGINPATH.'imanager/class/im.category.class.php');
// fields configurator class
include(GSPLUGINPATH.'imanager/class/im.fields.configurator.class.php');
// include output class
include(GSPLUGINPATH.'imanager/class/im.output.class.php');
// include reporter model class
include(GSPLUGINPATH.'imanager/class/im.msg.reporter.class.php');

// back-end
function imanager() { 
    $request = array_merge($_GET, $_POST);
    // init
    $manager = new ImController($request);
    // run
    echo $manager->displayBackEnd();
}
define('XMLTAG', '<?xml version="1.0" encoding="UTF-8" ?>');
?>
