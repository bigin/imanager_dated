<?php
/**
*    Plugin Name: Item Manager Manager
*    Description: Full Featured Account Manager.
*    Version: 0.7 Beta
*    Author: Bigin 
*    Author URI: http://ehret-studio.com
*
*    This file is part of Item Manager.
*
*    Item Manager is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by 
*    the Free Software Foundation, either version 3 of the License, or any 
*    later version.
*
*    Item Manager is distributed in the hope that it will be useful, but
*    WITHOUT ANY WARRANTY; without even the implied warranty of 
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU 
*    General Public License for more details.
*
*    You should have received a copy of the GNU General Public License along 
*    with Item Manager Extended.  If not, see <http://www.gnu.org/licenses/>.
*
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
  '0.7',
  'Bigin 07.03.2013',
  'http://www.ehret-studio.com',
  'Full featured Item Manager',
  'imanager',
  'imanager'
);
// activate actions
add_action('nav-tab', 'createNavTab', array('imanager', $thisfile, IMTITLE.' Manager', 'view')); 
/* include your own CSS for beautiful manager style */
register_style('imstyle', $SITEURL.'plugins/'.$thisfile.'/css/im-styles.css', GSVERSION, 'screen');
queue_style('imstyle', GSBOTH);

// model
include(GSPLUGINPATH.'imanager/class/im.model.class.php');
// controller
include(GSPLUGINPATH.'imanager/class/im.controller.class.php');
// category
include(GSPLUGINPATH.'imanager/class/im.category.class.php');
// configurator
include(GSPLUGINPATH.'imanager/class/im.fields.configurator.class.php');
// output
include(GSPLUGINPATH.'imanager/class/im.output.class.php');
// reporter
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
