<?php
/**
*    Plugin Name: Item Manager Extended
*    Description: Full Featured Items Manager.
*    Version: 0.6 Beta
*    Author: Bigin (modified plugin of PyC) 
*    Author URI: http://ehret-studio.de
*
*    This file is part of Item Manager Extended.
*
*    Item Manager Extended is free software: you can redistribute it 
*    and/or modify it under the terms of the GNU General Public License 
*    as published by the Free Software Foundation, either version 3 of 
*    the License, or any later version.
*
*    Item Manager Extended is distributed in the hope that it will be 
*    useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
*    of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License along 
*    with Item Manager Extended.  If not, see <http://www.gnu.org/licenses/>.
*
*/

# get correct id for plugin
$thisfile = basename(__FILE__, '.php');

# path & file constants definitions
define('PSPATH', GSPLUGINPATH.$thisfile.'/');
/*define('ITEMUPLOADDIR', 'data/uploads/imanager/');
define('ITEMUPLOADPATH', GSDATAPATH.'uploads/imanager/');
define('ITEMDATAFILE', GSDATAOTHERPATH.'imanager.xml');
define('IM_CUSTOMFIELDS_FILE', 'im.fields.xml');
define('IMCSSPATH', 'imanager/css/');
*/

// Initialize manager
/*$ititle = 'Item';
if(file_exists(ITEMDATAFILE))
{
    $im_datafile = getXML(ITEMDATAFILE);
    $ititle = (!empty($im_datafile->item->title) 
               ? $im_datafile->item->title : 'Item');
}*/
//define('TRANSCEIVERTITLE', $ititle);

// register plugin
register_plugin(
    $thisfile,
    'PlugShopProcessor',
    '0.1',
    'Bigin 24.08.2013',
    'http://ehret-studio.de',
    'PlugShop-Processor an E-Commerce module for GetSimple CMS to receive and process data.',
    'psprocessor',
    'genBackend'
);

// activate actions
add_action('plugins-sidebar','createSideMenu', array($thisfile, 'PS-Processor'), 'config');
add_action('content-top','run');
//add_action('nav-tab', 'createNavTab', array('imanager', $thisfile, IMTITLE.' Manager', 'view')); 
/* include your own CSS for beautiful manager style */
//register_style('imstyle', $SITEURL.'plugins/'.$thisfile.'/css/im-styles.css', GSVERSION, 'screen');
//queue_style('imstyle', GSBOTH);

include(PSPATH.'class/psp.controller.class.php');
/*
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
*/

function genBackend() 
{
    $request = array_merge($_GET, $_POST);
    // init
    $cr = new PspController($request);
    echo number_format($cr->number2float('13'), 2).'<br />';
    /*if($cr->is_value_float($cr->format_number(12.99)))
        echo 'Yes';
    else
        echo 'No';*/
    // run
    #echo $manager->displayBackEnd();
}

function run()
{
    $request = array_merge($_GET, $_POST);
    // init
    $cr = new PspController($request);

}
?>
