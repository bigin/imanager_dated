<?php
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified Mvlcek's plugin ) 
* Author URI: http://www.ehret-studio.de
*/
if(file_exists('ITEMDATAFILE'))
{
    $item_manager_file = getXML(GSDATAOTHERPATH.'item_manager.xml');
    global $item_title;
    $item_title = $item_manager_file->item->title;
} else {
    global $item_title;
    $item_title = 'Item';
}

require_once(GSPLUGINPATH.'items/inc/common.php');

$im_customfield_def = null;

function items_customfields_header() {
    if(file_exists(GSDATAOTHERPATH.IM_CUSTOMFIELDS_FILE))
        return;
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
	$xml->asXML(GSDATAOTHERPATH.IM_CUSTOMFIELDS_FILE);
	return true;
}
function items_customfields_configure($Reporter){
    include(GSPLUGINPATH.'items/inc/config.php');
}
