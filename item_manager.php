<?php

/**
* Plugin Name: Item Manager Extended
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/

/* ~Controller~ */

# get correct id for plugin
$thisfile = basename(__FILE__, '.php');
/*
# path & file constants definitions
define('ITEMDATA', GSDATAPATH.'items/');
define('ITEMUPLOADDIR', 'data/uploads/items/');
define('ITEMUPLOADPATH', GSDATAPATH.'uploads/items/');
define('ITEMDATAFILE', GSDATAOTHERPATH.'item_manager.xml');
define('IM_CUSTOMFIELDS_FILE', 'plugincustomfields.xml');
define('IMCSSPATH', 'items/css/');
# simplified names of templates
// admin header
define('ADMIN_HEADER', 'header.init.edit.tpl');

$item_manager_file = getXML(GSDATAOTHERPATH.'item_manager.xml');
$ISCAT = false;
// Initialize manager designations
if(file_exists(ITEMDATAFILE))
{
    $item_manager_file = getXML(ITEMDATAFILE);
    $item_title = (!empty($item_manager_file->item->title) 
                   ? $item_manager_file->item->title : 'Items');
} else
{
    $item_title = 'Item';
}
define('IMTITLE', $item_title);
*/
// register plugin
register_plugin(
  $thisfile,
  'Item Manager Extended',
  '0.5',
  'Bigin 07.03.2013 (modified plugin by PyC)',
  'http://www.ehret-studio.de',
  'Full featured Item Manager',
  '',//'item_manager',
  ''//'item_manager'
);
/*
// activate filter
add_action('nav-tab', 'createNavTab', array('item_manager', $thisfile, IMTITLE.' Manager', 'view'));
// put your own CSS for beautiful manager style 
add_action('header', 'imcss_showcss');

# i18n for back-end only
if(basename($_SERVER['PHP_SELF']) != 'index.php')
    i18n_merge('items') || i18n_merge('items','en_US');

// include category class
include(GSPLUGINPATH.'items/class/im.category.class.php');
// include output class
include(GSPLUGINPATH.'items/class/im.output.class.php');
// include former custom fields plugin
include(GSPLUGINPATH.'items/inc/custom.fields.configurator.php');
// include back-end functions file
include(GSPLUGINPATH.'items/inc/backend.php');
// include ItemsManager Class
include(GSPLUGINPATH.'items/class/IMclass.php');
// include DisplayItemsManager Class
include(GSPLUGINPATH.'items/class/IMclassDisplay.php');
// include language reporter class
include(GSPLUGINPATH.'items/class/IMreporter.php');
//i18n_merge('items') || i18n_merge('items','en_US');


// Navigation admin panel (back-end)
*/
function item_manager() 
{
    // At first we'll have to get the instances of that classes
    global $ISCAT;
	$ImClass = new ItemsManager;
    $ImReporter = new IMreporter;
    $ImRep = $ImReporter;
    // initialise category class
    $ImCat = new ImCategory(ITEMDATAFILE);
    // to check availability of category use: if($ImCat::$iscategory)
    
    // initialise output class
    $ImOut = new ImOutput(GSPLUGINPATH.'items/tpl/', $ImRep);

    //$ImOut->output(ADMIN_HEADER);

	//admin_header($ImReporter);

	if (isset($_GET['edit'])) 
	{
        $msg = catSelector($ImReporter, $ImReporter->getClause('items/handles'));
        if($ISCAT)
            showEditItem($ImClass, empty($_GET['edit']) ? uniqid() : $_GET['edit'], $ImReporter);
        else
            echo $msg;
	} 
	elseif (isset($_GET['delete'])) 
	{
		$ImClass->deleteItem($_GET['delete']);
        showItemsAdmin($ImClass, $ImReporter);
	} 
	elseif (isset($_GET['visible'])) 
	{
		$id = $_GET['visible'];
		$ImClass->switchVisibleItem($id);
        showItemsAdmin($ImClass, $ImReporter);
	}
	elseif (isset($_GET['promo'])) 
	{
		$id = $_GET['promo'];
		$ImClass->switchPromotedItem($id);
        showItemsAdmin($ImClass, $ImReporter);
	}
	elseif (isset($_POST['category_edit'])) 
	{      
		$ImClass->processImSettings();   
		showEditCategories($ImClass, $ImReporter);
	} 	
	elseif (isset($_GET['deletecategory']))
	{     
		$ImClass->processImSettings();   
		showEditCategories($ImClass, $ImReporter);   
	}		
	elseif (isset($_GET['category'])) 
	{       
		showEditCategories($ImClass, $ImReporter);	 
	}
	elseif (isset($_GET['settings_edit']))
	{
		$ImClass->processImSettings();
		showImSettings($ImReporter);
	}
	elseif (isset($_GET['settings'])) 
	{     
		showImSettings($ImReporter);
	}
	elseif (isset($_POST['submit'])) 
	{       
		$ImClass->processItem();

        $msg = catSelector($ImReporter, $ImReporter->getClause('items/handles'));
        if($ISCAT)
            showItemsAdmin($ImClass, $ImReporter);
        else
            echo $msg;
	}
	elseif (isset($_GET['fields']))
	{
        if(isset($_POST['sender']))
            $ImClass->processRenameItems();

		items_customfields_configure($ImReporter);
	}
	else 
	{
		$msg = catSelector($ImReporter, $ImReporter->getClause('items/handles'));
        if($ISCAT)
            showItemsAdmin($ImClass, $ImReporter);
        else
            echo $msg;
	}
}
?>
