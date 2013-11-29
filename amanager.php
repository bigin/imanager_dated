<?php
/**
*    Plugin Name: Account Manager
*    Description: Full Featured Account Manager.
*    Version: 0.1 Beta
*    Author: Bigin 
*    Author URI: http://ehret-studio.de
*
*    This file is part of Account Manager.
*
*    Account Manager is free software: you can redistribute it and/or modify 
*    it under the terms of the GNU General Public License as published by 
*    the Free Software Foundation, either version 3 of the License, or any 
*    later version.
*
*    Account Manager is distributed in the hope that it will be useful, but 
*    WITHOUT ANY WARRANTY; without even the implied warranty of 
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU 
*    General Public License for more details.
*
*    You should have received a copy of the GNU General Public License along 
*    with Account Manager. If not, see <http://www.gnu.org/licenses/>.
*
*/

# get correct id for plugin
$thisfile = basename(__FILE__, '.php');

# path & file constants definitions
#define('ITEMDATA', GSDATAPATH.'amanager/');
#define('ITEMUPLOADDIR', 'data/uploads/imanager/');
define('AMPLUGIN', $thisfile);
define('AMUPLOADPATH', GSDATAPATH.'uploads/amanager/');
#define('ITEMDATAFILE', GSDATAOTHERPATH.'imanager.xml');
#define('IM_CUSTOMFIELDS_FILE', 'im.fields.xml');
define('AMCSSPATH', AMPLUGIN.'/css/');
define('AMTITLE', 'Account Manager');


// register plugin
register_plugin(
  AMPLUGIN,
  AMTITLE,
  '0.1',
  'Bigin 17.08.2013',
  'http://ehret-studio.de',
  'Full featured '. AMTITLE,
  'amanager',
  'amanager'
);
// activate actions
add_action('nav-tab', 'createNavTab', array('amanager', AMPLUGIN, AMTITLE, 'view')); 
/* include your own CSS for beautiful manager style */
register_style('amstyle', $SITEURL.'plugins/'.AMCSSPATH.'style.css', GSVERSION, 'screen');
queue_style('amstyle', GSBOTH);

// model
include(GSPLUGINPATH.AMPLUGIN.'/class/am.model.class.php');
// controller
include(GSPLUGINPATH.AMPLUGIN.'/class/am.controller.class.php');
// user groups
include(GSPLUGINPATH.AMPLUGIN.'/class/am.groups.class.php');
// groups configurator
#include(GSPLUGINPATH.$thisfile.'imanager/class/im.fields.configurator.class.php');
// output
include(GSPLUGINPATH.AMPLUGIN.'/class/am.output.class.php');
// reporter
include(GSPLUGINPATH.AMPLUGIN.'/class/am.msg.reporter.class.php');

// back-end
function amanager() 
{ 
    $request = array_merge($_GET, $_POST);
    // init
    $am = new AmController($request);
    // run
    
    echo $am->displayBackEnd();
}
?>
