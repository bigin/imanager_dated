<?php
//ob_start();
//include(GSPLUGINPATH.'dominion-jcart/jcart.ini.php');
//global $inifile;
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
define('CARTDATAPATH', GSDATAPATH.'scart/');
//define('UPLOADDIR', 'data/uploads/scart/');
//define('ITEMUPLOADPATH', GSDATAPATH.'uploads/imanager/');
//define('ITEMDATAFILE', GSDATAOTHERPATH.'imanager.xml');
//define('IM_CUSTOMFIELDS_FILE', 'im.fields.xml');
//define('IMCSSPATH', 'imanager/css/');


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



$inifile = dirname(getcwd()).$settings['file']['inifile'];

global $uplddir;
$uplddir = substr($settings['path']['img-upload-path'], 0, -1);

if (strpos($_SERVER ['REQUEST_URI'],'/admin/') === false) {
    //if in admin panel then this is not needs
    include $settings['file']['jcart.php'];
}

session_start();
# get correct id for plugin
$thisfile=basename(__FILE__, '.php');

# register plugin
register_plugin(
	$thisfile, 	# ID of plugin, should be filename minus php
	'jCart', 	# Title of plugin
	'0.2', 		# Version of plugin
	'J.E. www.ehret-studio.de',	# Author of plugin
	'http://www.ehret-studio.de', 	# Author URL
	'Modified jCart system for getSimple .', 	# Plugin Description
	'plugins', 	# Page type of plugin
	'show_jcart_config'  	# Function that displays content
);

# activate filter
add_filter('content','content_dcart_show'); 
add_action('plugins-sidebar','createSideMenu',array($thisfile,'jCart'));
add_action('theme-header','set_dcart_headers');
//add_action('index-pretemplate','dcart_start_template');
//add_action('index-posttemplate','dcart_end_template');

/*
  Filter Content for dcart markers (%cart_id%)
    the cart of that id will be inserted in the markers section of the conent
*/
//header( 'Location: http://ajaluithle.de');
function content_dcart_show($contents){
   // $bgColor = implode("",@file(GSDATAOTHERPATH. '/mp3playerextended.cfg'));
    $tmpContent = $contents;
	preg_match_all('/\(%(.*)dcart(.*):(.*)%\)/i',$tmpContent,$tmpArr,PREG_PATTERN_ORDER);
    $AlltoReplace = $tmpArr[count($tmpArr)-1];
    $totalToReplace = count($AlltoReplace);
    for ($x = 0;$x < $totalToReplace;$x++) {
       $targetCart= str_replace('&nbsp;',' ',$AlltoReplace[$x]);
       $targetCart = trim($targetCart);
      $adTeks = buildCart($targetCart);
      $tmpContent = preg_replace("/\(%(.*)dcart(.*):(.*)$targetCart(.*)%\)/i",$adTeks,$tmpContent);
    }
    //ob_end_flush();   
    return $tmpContent;
}

/*
  * Show the config menu for the user..
  *
*/
function show_jcart_config(){
    global $SITEURL;
    $webURL = preg_replace('/&del=.*/i','',$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    // define output
    $output = '';
    //print_r($_SESSION['art']);
    $output .= '<h2>Artikelmanagement Panel</h2>';
    $output .= '<div style="height:30px;"><a href=""><img style="float:left;" alt="" src="'.$SITEURL.'theme/Wertpaper/i/reload.png" border="0" /></a>';
    $output .= ' &nbsp;<p style="float:left;padding-top:8px;margin-left:5px;"><a href="" >Reload</a></p></div>';
    $output .= '<p style="clear:left;">Hier können Sie einen neuen Artikel anlegen oder ändern Parameter</em> '."\n";
    $output .= 'von bereits bestehenden Artikeln.</p>'."\n";

    $output .= '<div class="backscreen">'."\n";
    $output .= '<fieldset style="padding:20px;">'."\n";
    $output .= '    <legend>Bearbeiten</legend>'."\n";
    $output .= '    <form id="get_id_form" name="get_id_form" action="#save_art" method="POST" >'."\n";
    $output .= '        <label class="artid" for="artid">Artikel-ID</label>'."\n";
    $output .= '        <input type="number" style="width:70px;" name="artid" /><br /><br />'."\n";
    $output .= '        <input type="submit" class="submit" name="getidsend" value="Laden" />'."\n";
    $output .= '    </form>'."\n";
    // when clicked "Load ID"
    $output .= '    '.integrateUpdateForm();
    $output .= '</fieldset>'."\n";

    $output .= '<fieldset style="padding:20px; margin-top:50px;">'."\n";
    $output .= '    <legend>Artikelpositionen tauschen</legend>'."\n";
    $output .= '    <form id="replace_id_form" name="replace_id_form" action="#replace_id_form" method="POST" >'."\n";
    $output .= '        <label class="artid" for="artid1">1. Artikel-ID</label>'."\n";
    $output .= '        <input type="number" style="width:70px;" name="artid1" /><br /><br />'."\n";
    $output .= '        <label class="artid" for="artid2">2. Artikel-ID</label>'."\n";
    $output .= '        <input type="number" style="width:70px;" name="artid2" /><br /><br />'."\n";
    $output .= '        <input type="submit" class="submit" name="replaceidsend" value="Tauschen" />'."\n";
    $output .= '    </form>'."\n";
    $output .= '</fieldset>'."\n";


    $output .= '<fieldset style="padding:20px; margin-top:50px;">'."\n";
    $output .= '    <legend>Neuer Artikel</legend>'."\n";
    $output .= '    <form id="new_art_form" name="new_art_form" action="#gen_art" method="POST" >'."\n";
    $output .= '        <p><strong>Artikel-ID wird automatisch vom System erstellt</strong></p>';
    $output .= '        <input type="hidden" name="newart" value="1" /><br /><br />'."\n";
    $output .= '        <input type="submit" class="submit" name="new_send" value="Generieren" />'."\n";
    $output .= '    </form>'."\n";
    $output .= '    '.integrateGenForm();
    $output .= '</fieldset>'."\n";
    
    // change inits position
    if(isset($_POST['replaceidsend'])) {
        if(checkIdsToChange())
            echo '<script>alert("Zwei Artikel wurden erfolgreich aktualisiert!");'.
                 ' window.location = "http://'.$webURL.'"</script>';
        else
            echo '<script>alert("Ein Fehler ist aufgetreten: Artikel-Update ist fehlgeschlagen!");'.
                 ' window.location = "http://'.$webURL.'"</script>';
    }
    // Generate new init
    if(isset($_POST['gen_art'])) {
        if(checkNewInitValues()) {
            if(generateInit()) {
                echo '<script>alert("Ein neuer Artikel wurde erfolgreich erstellt!");'.
                      ' window.location = "http://'.$webURL.'"</script>';
            } else {
                echo '<script>alert("Ein Fehler ist aufgetreten:'."\n".'Neuer Artikel konnte nicht '.
                     'erstellt werden!"); window.location = "http://'.$webURL.'"</script>';
            }
        }
    }
    // Update init
    if(isset($_POST['save_art'])) {
        if(checkNewInitValues(false)) {
            if(updateInit())
                echo '<script>alert("Artikel wurde erfolgreich aktualisiert!");'.
                     ' window.location = "http://'.$webURL.'"</script>';
            else
                echo '<script>alert("Ein Fehler ist aufgetreten: Artikel-Update ist fehlgeschlagen!");'.
                      ' window.location = "http://'.$webURL.'"</script>';
        }
    }

    // generate backup file
    if(isset($_POST['gen_backup'])) {
        if(genBackup())
            echo '<script>alert("Die Backup-Datei wurde erfolgreich erstellt!");'."\n".
                 ' window.location = "http://'.$webURL.'";</script>';
        else
            echo '<script>alert("Ein Fehler ist aufgetreten: Die Backup-Datei konnte nicht erstellt werden!");'.
                 ' window.location = "http://'.$webURL.'"</script>';
        
    }

    // delete backup file
    if(isset($_GET['del']) && intval($_GET['del']) != 0) {
        if(delBackup()) {
            echo '<script type="text/javascript">alert("Sicherheitskopie back_'.intval($_GET['del'])
                  .'.ini wurde erfolgreich gelöscht.");'."\n".' window.location = "http://'.$webURL.'";</script>';
        } else {
            echo '<script type="text/javascript">alert("Sicherheitskopie back_'.intval($_GET['del'])
                  .'.ini könnte nicht gelöscht werden.");'."\n".
                 ' window.location = "http://'.$webURL.'";</script>';
        }
    }
   // print_r($_SERVER);
    

    $output .= displayIni();

    $output .= displayBackupPanel();
    
    $output .= '</div>'."\n";
    echo $output;
}

/*
* Try to integrate update form
*/
function integrateUpdateForm() {
    $output = '';
    if(!isset($_POST['getidsend']) || !isset($_POST['artid']) || intval($_POST['artid'])<=0)
        return;
    global $inifile;
    $iniItems = parse_ini_file($inifile,true);
    //item_backup = array();
    
    if(isset($iniItems[intval($_POST['artid'])])) {
        
        $_SESSION['itemsold'] = $iniItems[intval($_POST['artid'])]['sold'];
        $output .= '<div class="upwrapp" style="margin-top:50px;">'."\n";
        $output .= '    <form id="save_art" name="save_art" action="#" method="POST" >'."\n";
        // ID
        $output .= '        <label for="artid">Artikel-ID</label>'."\n";
        $output .= '        <input type="number" readonly style="width:70px;" name="artid"';
        $output .=          ' value="'.intval($_POST['artid']).'"/><br /><br />'."\n";
        // Name
        $output .= '        <label for="artname">Name</label>'."\n";
        $output .= '        <input type="text" name="artname" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['name'] ? 
                            $iniItems[intval($_POST['artid'])]['name'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Year
        $output .= '        <label for="artyear">Jahr</label>'."\n";
        $output .= '        <input type="text" name="artyear" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['year'] ? 
                            $iniItems[intval($_POST['artid'])]['year'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Cat
        $output .= '        <label for="artcat">Kategorie</label>'."\n";
        $output .= '        <input type="text" name="artcat" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['cat'] ? 
                            $iniItems[intval($_POST['artid'])]['cat'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Flag
        $output .= '        <label for="artflag">Artikel deaktivieren <span style="font-size:xx-small;';
        $output .=          'color:#999;">Zahl 1 = Deaktivieren</span></label>'."\n";
        $output .= '        <input type="number" name="artflag" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['flag'] ? 
                            $iniItems[intval($_POST['artid'])]['flag'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Auflage
        $output .= '        <label for="artex">Auflage </label>'."\n";
        $output .= '        <input type="number" name="artex" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['ex'] ? 
                            $iniItems[intval($_POST['artid'])]['ex'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Sold
        $output .= '        <label for="artsold">Verkauft </label>'."\n";
        $output .= '        <input type="number" name="artsold" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['sold'] ? 
                            $iniItems[intval($_POST['artid'])]['sold'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Price
        $output .= '        <label for="artextprice">Preis <span style="font-size:xx-small;color:#999;">';
        $output .=          'Format 123.23 (Punkt statt Komma!)</span></label>'."\n";
        $output .= '        <input type="text" name="artprice" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['price'] ? 
                            $iniItems[intval($_POST['artid'])]['price'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Ext_price
        $output .= '        <label for="artextprice">Angebotspreis <span style="font-size:xx-small;';
        $output .=          'color:#999;">Format 123.23 (Punkt statt Komma!)</span></label>'."\n";
        $output .= '        <input type="text" name="artextprice" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['ext_price'] ? 
                            $iniItems[intval($_POST['artid'])]['ext_price'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Ext_date
        $output .= '        <label for="artextdate">Angebot bis <span style="font-size:xx-small;';
        $output .=          'color:#999;">Bsp. 02.12.2012</span></label>'."\n";
        $output .= '        <input type="text" name="artextdate" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['ext_date'] ? 
                            $iniItems[intval($_POST['artid'])]['ext_date'] : '';
        $output .=          '"/><br /><br />'."\n";
        // Description
        $output .= '        <label for="artdescr">Artikelbeschreibung <span style="font-size:xx-small;';
        $output .=          'color:#999;">HTML -Tags können verwendet werden ';
        $output .=          '&lt;br /&gt; &lt;p&gt;mein text...&lt;/p&gt; etc..</span></label>'."\n";
        $output .= '        <textarea type="text" style="width:600px; height:100px;" name="artdescr">';
        $output .=          $iniItems[intval($_POST['artid'])]['descr'] ? 
                            $iniItems[intval($_POST['artid'])]['descr'] : '';
        $output .=          '</textarea><br /><br />'."\n";
        // Image name
        $output .= '        <label for="artimage">Bildname mit Dateiendung ';
        $output .=          '<span style="font-size:xx-small;color:#999;">(.jpg, .png, etc...) ';
        $output .=          'Keine Sonderderzeichen und keine Leerzeichen verwenden!</span></label>'."\n";
        $output .= '        <input type="text" name="artimage" value="';
        $output .=          $iniItems[intval($_POST['artid'])]['imgname'] ? 
                            $iniItems[intval($_POST['artid'])]['imgname'] : '';
        $output .=          '"/><br /><br />'."\n";
        
        $output .= '        <input type="submit" name="save_art" value="Speichern" />';

        $output .= '    </form>'."\n";
        $output .= '</div>'."\n";
    }
    return $output;
}

function integrateGenForm() {
    $output = '';
    if(!isset($_POST['new_send']) || !isset($_POST['new_send']) || intval($_POST['newart'])<=0)
        return;
    global $inifile;
    $iniItems = parse_ini_file($inifile,true);

    foreach($iniItems as $key => $val)
        $keys[] = $key;
    $nexid = max($keys) + 1;

    $output .= '<div class="upwrapp" style="margin-top:50px;">'."\n";
    $output .= '    <form id="gen_art" name="gen_art" action="#" method="POST" >'."\n";
    // ID
    $output .= '        <label for="artid">Artikel-ID</label>'."\n";
    $output .= '        <input type="number" readonly style="width:70px;" name="artid"';
    $output .=          ' value="'.$nexid.'"/><br /><br />'."\n";
    // Name
    $output .= '        <label for="artname">Name</label>'."\n";
    $output .= '        <input type="text" name="artname" value=""/><br /><br />'."\n";
    // Year
    $output .= '        <label for="artyear">Jahr</label>'."\n";
    $output .= '        <input type="text" name="artyear" value=""/><br /><br />'."\n";
    // Cat
    $output .= '        <label for="artcat">Kategorie</label>'."\n";
    $output .= '        <input type="number" name="artcat" value=""/><br /><br />'."\n";
    // Flag
    $output .= '        <label for="artflag">Artikel deaktivieren ';
    $output .=          '<span style="font-size:xx-small;color:#999;">Zahl 1 = Deaktivieren</span></label>'."\n";
    $output .= '        <input type="number" name="artflag" value=""/><br /><br />'."\n";
    // Auflage
    $output .= '        <label for="artex">Auflage</label>'."\n";
    $output .= '        <input type="number" name="artex" value=""/><br /><br />'."\n";
    // Sold
    $output .= '        <label for="artsold">Verkauft</label>'."\n";
    $output .= '        <input type="number" name="artsold" value=""/><br /><br />'."\n";
    // Price
    $output .= '        <label for="artprice">Preis <span style="font-size:xx-small;color:#999;">';
    $output .=          'Preisformat 123.23 (Punkt statt Komma!)</span></label>'."\n";
    $output .= '        <input type="text" name="artprice" value=""/><br /><br />'."\n";
    // Ext_price
    $output .= '        <label for="artextprice">Angebotspreis <span style="font-size:xx-small;color:#999;">';
    $output .=          'Preisformat 123.23 (Punkt statt Komma!)</span></label>'."\n";
    $output .= '        <input type="text" name="artextprice" value=""/><br /><br />'."\n";
    // Ext_date
    $output .= '        <label for="artextdate">Angebot bis <span style="font-size:xx-small;color:#999;">';
    $output .=          '(Bsp. 02.12.2012)</span></label>'."\n";
    $output .= '        <input type="text" name="artextdate" value=""/><br /><br />'."\n";
    // Description
    $output .= '        <label for="artdescr">Artikelbeschreibung ';
    $output .=          '<span style="font-size:xx-small;color:#999;">HTML -Tags können verwendet werden ';
    $output .=          '&lt;br /&gt; &lt;p&gt;mein text...&lt;/p&gt; etc..</span></label>'."\n";
    $output .= '        <textarea type="text" style="width:600px; height:100px;" name="artdescr"></textarea>';
    $output .=          '<br /><br />'."\n";
    // Image name
    $output .= '        <label for="artimage">Bildname mit Dateiendung <span style="font-size:xx-small;color:#999;"';
    $output .=          '>(.jpg, .png, etc...) Keine Sonderderzeichen und keine Leerzeichen '."\n";
    $output .=          'verwenden!</span></label><input type="text" name="artimage" value=""/><br /><br />'."\n";
        
    $output .= '        <input type="submit" name="gen_art" value="Speichern" />';

    $output .= '    </form>'."\n";
    $output .= '</div>'."\n";
    
    return $output;
}

// Liste display
function displayIni(){
    global $inifile;
    $iniItems = parse_ini_file($inifile,true);
    // array all keys
    $allkeys = array_keys($iniItems);
    // define array only enabled keys
    $actkeys = null;

    // number of all keys
    $itemnum = count($allkeys);
    // fuel enabled keys array 
    foreach($allkeys AS $id)
        if($iniItems[$id]['flag'] == 0)
            $actkeys[] = $id;
    $actitemnum = count($actkeys);
    
    $output = '';
    $output .= '<h2 style="margin-top:50px;">Artikel Liste</h2>';
    $output .= '<p>Mit <span style="color:#f74600;"><strong>orange</strong></span> markierte Bilder sind ';
    $output .= 'nicht verfügbar.<br />Aktuell sind im Shop <strong>'.$itemnum.'</strong> Artikel eingetragen.';
    $output .= ' Davon sind <strong>'.$actitemnum.'</strong> freigeschaltet und für alle Shop-Besucher zugänglich.</p>';
    $output .= '<div style="height:520px;overflow:auto;padding:5px;background-color:#F6F6F6">';
    foreach($iniItems AS $key => $val) {
        $output .= '<p><span style="display:block; float:left; width:120px;">';
        $output .= 'Artikel ID: </span><strong style="color:red">'.$key.'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Name: </span><strong>'.$iniItems[$key]['name'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Jahr: </span><strong>'.$iniItems[$key]['year'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Kategorie: </span><strong>'.$iniItems[$key]['cat'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Artikel deaktiviert: </span><strong>'.$iniItems[$key]['flag'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Auflage: </span><strong>'.$iniItems[$key]['ex'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Verkauft: </span><strong>'.$iniItems[$key]['sold'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Preis: </span><strong>'.$iniItems[$key]['price'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Angebotspreis: </span><strong>'.$iniItems[$key]['ext_price'].'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px;">';
        $output .= 'Angebot endet am: </span><strong>'.$iniItems[$key]['ext_date'].'</strong><br />';
        $output .= '<span style="display:block; float:left; ">';
        $output .= 'Bilder-Verfügbarkeit: </span><strong>'
                   .checkImageAvailability($iniItems[$key]['imgname']).'</strong><br />';
        $output .= '<span style="display:block; float:left; width:120px; height:50px;">';
        $output .= 'Beschreibung: </span><strong>'.htmlspecialchars($iniItems[$key]['descr']);
        $output .= '</strong><br /><br /></p>';
    }
    $output .= '</div>';

    return $output;
}
/* BACKUP INI FILE MANAGEMENT PANEL*/
function displayBackupPanel() {
    global $settings;
    $backfiles = glob($settings['path']['jcart-path'].$settings['path']['ini-backup-path'].
                      $settings['backup-file-search-pattern']);
    $copi = count((is_array($backfiles) ? $backfiles : NULL));   

    $scop = 'Momentane Anzahl der Sicherheitskopien: <strong>'.$copi.'</strong>';
    $geninput = '<form id="gen_art" style="margin-top:50px;" name="gen_backup" action="#" method="POST" ><input type="submit"';
    $geninput .= ' class="submit" name="gen_backup" value="Backup generieren"/></form>';


    $output = '';
    $output .= '<h2 style="margin-top:50px;">Backup Management Panel</h2>';
    $output .= '<p>Erstellen Sie eine neue Sicherheitskopie Ihrer aktuellen Produktliste.<br />'.$scop.'<br />';
    $output .= 'Die letzte Sicherheitskope ist mit <strong style="color:red;">rot</strong> markiert.</p>';
    $output .= '<div>';
    $output .= listFile($settings['path']['jcart-path'].$settings['path']['ini-backup-path'], 
                        $settings['backup-file-search-pattern'], $copi);
    $output .= $geninput; 
    $output .= '</div>';
    return $output;
}


function listFile($dir,$type,$num) {
    $x = 0;
    $output = '';
    //echo $num;
    foreach (glob($dir.$type,GLOB_BRACE|GLOB_ERR) as $filename) {
        $style = '';
        if(($x+1) == $num)
            $style = 'style="color:red;"';
        $output .= '<p style="margin:0px;">Name: <strong '.$style.'>'.basename($filename).'</strong>';
        $output .= '&nbsp;&nbsp;&nbsp;&nbsp;Size: <strong '.$style.'>';
        $output .=  (filesize($filename) / 1024).'</strong>&nbsp;&nbsp;&nbsp;&nbsp;Time: <strong '.$style.'>';
        $output .=  date('d.m.Y - H:i',filemtime($filename)).'</strong>&nbsp;&nbsp;&nbsp;&nbsp;';
        $output .=  '<a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&del='.filemtime($filename)
                    .'" >löschen</a></p>';
        $x++;
    } 
    
    return $output;
}

function genBackup() {
    //global $inifile;
    global $settings;
    //dirname(getcwd())
    
    if(copy(dirname(getcwd()).$settings['file']['inifile'], dirname(dirname(getcwd()).$settings['file']['inifile']).
            '/'.$settings['path']['ini-backup-path'].'back_'.time().'.ini'))
        return true;
    return false;
}

function delBackup() {
    global $settings;
    if(isset($_GET['del']) && intval($_GET['del']) != 0) {
        if(@unlink(dirname(dirname(getcwd()).$settings['file']['inifile']).
           '/'.$settings['path']['ini-backup-path'].'back_'.intval($_GET['del']).'.ini')) {
            return true;    
        } else {
            return false;
        }
    }
}
function checkImageAvailability($iname) {
    $output = '';
    global $settings;
    $uplddir = $settings['file']['img-upload-dir'];
    global $SITEURL;
    
    $url = 'http://www.';
    $url .= str_replace('http://', '', $SITEURL);
    // http://www.anjaluithle-wertpapier.de/admin/upload.php?path=sammlung2012

    $sm_imgname_pur = substr($iname, 0, strrpos($iname, '.'));
    $sm_image_suffix = substr(strrchr($iname, '.'), 1);
    $sm_image = $sm_imgname_pur.'_sm.'.$sm_image_suffix;
    $bg_image = $sm_imgname_pur.'_bg.'.$sm_image_suffix;
    //echo GSDATAUPLOADPATH.$uplddir.'/'.$iname.'<br />';
    // Gallery image
    if( file_exists(GSDATAUPLOADPATH.$uplddir.'/'.$iname) )
        $output .= '<a style="padding-left:10px;padding-right:20px;color:#23c323;" href="'
                   .$url.'admin/upload.php?path='.$uplddir.'" >'.$iname.'</a>';
    else
        $output .= '<a style="padding-left:10px;padding-right:20px;color:#f74600;" href="'
                   .$url.'admin/upload.php?path='.$uplddir.'" >'.$iname.'</a>';
    // SM image
    if( file_exists(GSDATAUPLOADPATH.$uplddir.'/sm/'.$sm_image) )
        $output .= '<a style="padding-right:20px;color:#23c323;" href="'
                   .$url.'admin/upload.php?path='.$uplddir.'/sm" >'.$sm_image.'</a>';
    else
        $output .= '<a style="padding-right:20px;color:#f74600;" href="'
                   .$url.'admin/upload.php?path='.$uplddir.'/sm" >'.$sm_image.'</a>';
    // BG image
    if( file_exists(GSDATAUPLOADPATH.$uplddir.'/bg/'.$bg_image) )
        $output .= '<a style="color:#23c323;"href="'
                   .$url.'admin/upload.php?path='.$uplddir.'/bg" >'.$bg_image.'</a>';
    else
        $output .= '<a style="color:#f74600;" href="'
                   .$url.'admin/upload.php?path='.$uplddir.'/bg" >'.$bg_image.'</a>';

    return $output;
}

function checkNewInitValues($f=true) {
    global $inifile;
    $iniItems = parse_ini_file($inifile,true);

    foreach($iniItems as $key => $val)
        $keys[] = $key;
    $nexid = max($keys) + 1;
    // kollision mit 2 user!!!
    if(isset($f) && $f) {
        if(!isset($_POST['artid']) || $_POST['artid'] != $nexid)
            return false;
    }
    $_SESSION['art']['artid'] = intval($_POST['artid']);
    
    if(!isset($_POST['artname']) || $_POST['artname']=='')
       $_SESSION['art']['artname'] = 'Thumb-Artikel';
    else
       $_SESSION['art']['artname'] = $_POST['artname'];
     
    if(!isset($_POST['artyear']) || $_POST['artyear']=='')
        $_SESSION['art']['artyear'] = date("Y");
    else
        $_SESSION['art']['artyear'] = $_POST['artyear'];

    if(!isset($_POST['artcat']) || $_POST['artcat']=='')
       $_SESSION['art']['artcat'] = 1;
    else
        $_SESSION['art']['artcat'] = intval($_POST['artcat']);

    if(!isset($_POST['artflag']) || $_POST['artflag']=='')
       $_SESSION['art']['artflag'] = 0;
    else
        $_SESSION['art']['artflag'] = intval($_POST['artflag']);

    if(!isset($_POST['artex']) || $_POST['artex'] <= 0)
        $_SESSION['art']['artex'] = 1;
    else
        $_SESSION['art']['artex'] = intval($_POST['artex']);

    if(!isset($_POST['artsold']) || $_POST['artsold']=='')
       $_SESSION['art']['artsold'] = 0;
    else
        $_SESSION['art']['artsold'] = intval($_POST['artsold']);

    if(!isset($_POST['artprice']) || $_POST['artprice']=='')
       $_SESSION['art']['artprice'] = 0;
    else
        $_SESSION['art']['artprice'] = $_POST['artprice'];

    if(!isset($_POST['artextprice']) || $_POST['artextprice']=='')
       $_SESSION['art']['artextprice'] = 0;
    else
        $_SESSION['art']['artextprice'] = $_POST['artextprice'];

    if(!isset($_POST['artextdate']) || $_POST['artextdate']=='')
        $_SESSION['art']['artextdate'] = '';
    else
        $_SESSION['art']['artextdate'] = $_POST['artextdate'];
    
    if(!isset($_POST['artdescr']))
       $_SESSION['art']['artdescr'] = '';
    else
        $_SESSION['art']['artdescr'] = $_POST['artdescr'];

    if(!isset($_POST['artimage']))
       $_SESSION['art']['artimage'] = '';
    else
        $_SESSION['art']['artimage'] = $_POST['artimage'];

    return true;
}

function checkIdsToChange(){
    if(!isset($_POST['artid1']) OR intval($_POST['artid1'])==0)
        return false;
    if(!isset($_POST['artid2']) OR intval($_POST['artid2'])==0)
        return false;
    if(intval($_POST['artid1']) == intval($_POST['artid2']))
        return false;
    global $inifile;
    $iniItems = parse_ini_file($inifile,true);
    if(!isset($iniItems[intval($_POST['artid1'])]) OR 
       !isset($iniItems[intval($_POST['artid2'])]))
        return false;
    global $inifile;
    global $settings;
    include $settings['file']['jcart-gateway-mail.php'];
    if(replaceInitsPosition(intval($_POST['artid1']), intval($_POST['artid2']), $inifile))
        return true;
        
    return false;

}

function generateInit() {
    global $inifile;
    global $settings;
    include $settings['file']['jcart-gateway-mail.php'];
    if(writeNewInit($inifile))
        return true;
    return false;
}

function updateInit() {
    global $inifile;
    $iniItems = parse_ini_file($inifile,true);
    global $settings;
    include $settings['file']['jcart-gateway-mail.php'];
    $f = false;
    $art =& $_SESSION['art'] ? $_SESSION['art'] : false;
    
    if(!$art)
        return false;
    
    //echo $_SESSION['itemsold'];
    $itemsold =& $_SESSION['itemsold'];
    if(!isset($itemsold)) {
        return false;
    }
       
    // name
    if($art['artname'] != $iniItems[$art['artid']]['name']) {
        writeIni($art['artid'], 'name', $art['artname'], $inifile, '');
        $f=true;
    }
    // year
    if($art['artyear'] != $iniItems[$art['artid']]['year']) {
        writeIni($art['artid'], 'year', $art['artyear'], $inifile, '');
        $f = true;
    }
    // categorie
    if($art['artcat'] != $iniItems[$art['artid']]['cat']) {
        writeIni($art['artid'], 'cat', $art['artcat'], $inifile, '');
        $f = true;
    }
    // flag
    if($art['artflag'] != $iniItems[$art['artid']]['flag']) {
        writeIni($art['artid'], 'flag', $art['artflag'], $inifile, '');
        $f = true;
    }
    // ex
    if($art['artex'] != $iniItems[$art['artid']]['ex']) {
        writeIni($art['artid'], 'ex', $art['artex'], $inifile, '');
        $f = true;
    }
    // sold
    //echo $art['artsold'].' - '.$_SESSION['itemsold'];
    if(isset($_SESSION['itemsold']) && $art['artsold'] != intval($_SESSION['itemsold'])) {
        writeIni($art['artid'], 'sold', ($art['artsold']-intval($_SESSION['itemsold'])), $inifile, '', true);
        $f = true;
    }
    // price
    if($art['artprice'] != $iniItems[$art['artid']]['price']) {
        writeIni($art['artid'], 'price', $art['artprice'], $inifile, '');
        $f = true;
    }
    // ext_price
    if($art['artextprice'] != $iniItems[$art['artid']]['ext_price']) {
        writeIni($art['artid'], 'ext_price', $art['artextprice'], $inifile, '');
        $f = true;
    }
    // ext_date
    if($art['artextdate'] != $iniItems[$art['artid']]['ext_date']) {
        writeIni($art['artid'], 'ext_date', $art['artextdate'], $inifile, '');
        $f = true;
    }
    // descr
    if($art['artdescr'] != $iniItems[$art['artid']]['descr']) {
        writeIni($art['artid'], 'descr', $art['artdescr'], $inifile, '');
        $f = true;
    }
    // image
    if($art['artimage'] != $iniItems[$art['artid']]['imgname']) {
        writeIni($art['artid'], 'imgname', $art['artimage'], $inifile, '');
        $f = true;
    }

    if($f)
        return true;
    return false;
}

/*
* Set dCart headers
*/
function set_dcart_headers(){
    global $SITEURL; 
    global $settings; 
    $jsPath = $SITEURL.$settings['path']['jcart-js-path'].$settings['file']['jquery'];
    $jsDragPath = $SITEURL.$settings['path']['jcart-js-path'].$settings['file']['jquery-ui'];

    echo '<script type="text/javascript" src="'.$jsPath.'"></script>'."\n";
    echo '<script type="text/javascript" src="'.$SITEURL;
    echo 'plugins/dominion-jcart/js/jcart-javascript.php?base='.$SITEURL.'"></script>'."\n";        
    echo '<script type="text/javascript" src="'.$SITEURL.'plugins/dominion-jcart/js/jquery.evtpaginate.js">';
    echo '</script>'."\n".'<script src="'.$jsDragPath.'" type="text/javascript"></script>'."\n";
}

function buildCart($targetCart) {
    // Check validity of the offer
    //include GSPLUGINPATH .'dominion-jcart/jcart-offer.php';
    //je_checkOffer();
    //echo 'TEST';
    //exit();
    global $settings;
    //>global $dominion_jcart_path;
    //>global $dominion_jcart_cat_file;
    
    $dcartcustomer =& $_SESSION['dcartcustomer'];

    // Redirect to the "Danke" page
    
    if (isset($_GET['final_gw']) && $_GET['final_gw'] == '4') {
        if((!isset($_SESSION['jcart']) || !$_SESSION['jcart']) || 
           (!isset($dcartcustomer) || !$dcartcustomer || $dcartcustomer['customer_name'] == ' '))
            return 'No data available.';
        $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();

         // VERSANDRECHNER ///////
        $total_ship = 0;
        if($cart->itemcount > 6) {
            $total_ship = (float)($cart->total + 15.00);
        } else {
            $total_ship = (float)($cart->total + 8.00);
        }
        if($cart->total >= 500.00){
            $total_ship = (float)($cart->total + 0.00);
        }
        get_component('soundintegrator');
        echo '<h1 class="h-std">Vielen Dank für Ihre Bestellung!</h1>';
        echo '<p class="stdtext"><br /><br />Bitte überweisen Sie den Betrag in Höhe von <strong>'
             .number_format((double)$total_ship,2,",",".").' Euro</strong> auf folgendes Konto:<br /><br /></p>';
        echo '<ul class="stdtext">';
        echo '<li>Volksbank Stuttgart eG</li>';
        echo '<li>Empfänger: Anja Luithle</li>';
        echo '<li>Kontonummer: 193500000</li>';
        echo '<li>Bankleitzahl: 60090100</li>';
        echo '<li>Verwendungszweck: '.intval($_SESSION['inv_num']).'</li>';
        echo '</ul>';
        echo '<p class="stdtext"><br /><br />Der Versand erfolgt in 3-12 Werktagen nach Zahlungseingang.</p>';
        echo '<p class="stdtext">Sie erhalten eine Mail als Bestätigung Ihrer Bestellung.</p>';

        $dcartcustomer = array();
        $cart->empty_cart();
        unset($_SESSION['inv_num']);
        session_destroy();


        return;
        //EMPTY THE CART
    }

    // Sende Rechnung per Mail 
    if (isset($_GET['final_gw']) && $_GET['final_gw'] == '3') {
        if((!isset($_SESSION['jcart']) || !$_SESSION['jcart']) || 
           (!isset($dcartcustomer) || !$dcartcustomer || $dcartcustomer['customer_name'] == ' '))
            return 'No data available.';
        $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart(); 
    
        include $settings['file']['jcart-gateway-mail.php'];
    
        return getDominionPaymentPage(
                                        $dcartcustomer,
                                        $cart,
                                        intval($_POST['slipnumber'])
                                     );

    }
    

    if (isset($_GET['final_gw']) && $_GET['final_gw'] == '2') {
    //do final payment gateay as configured (config to come later)
        if (isset($_POST['name']))  {
            $dcartcustomer['customer_vname'] = (isset($_POST['vname'])) ? strip_tags($_POST['vname']) : '';
            $dcartcustomer['customer_nname'] = strip_tags($_POST['name']);
        
            $dcartcustomer['customer_name'] = strip_tags($_POST['vname']).' '.strip_tags($_POST['name']);
            $dcartcustomer['customer_email'] = (isset($_POST['email'])) ? strip_tags($_POST['email']) : '';
            $dcartcustomer['customer_company'] = (isset($_POST['firma'])) ? strip_tags($_POST['firma']) : '';
            $dcartcustomer['customer_strasse'] = (isset($_POST['strasse'])) ? strip_tags($_POST['strasse']) : '';
            $dcartcustomer['customer_plz'] = (isset($_POST['plz'])) ? strip_tags($_POST['plz']) : '';
            $dcartcustomer['customer_land'] = (isset($_POST['land'])) ? strip_tags($_POST['land']) : '';
            $dcartcustomer['customer_ort'] = (isset($_POST['ort'])) ? strip_tags($_POST['ort']) : '';
            $dcartcustomer['customer_msg'] = (isset($_POST['msg'])) ? strip_tags(trim($_POST['msg'])) : '';
        
            $buf = (isset($_POST['l_vname'])) ? strip_tags($_POST['l_vname']) : '';
        
            $dcartcustomer['customer_l_vname'] = (isset($_POST['l_vname'])) ? strip_tags($_POST['l_vname']) : '';
            $dcartcustomer['customer_l_nname'] = (isset($_POST['l_name'])) ? strip_tags($_POST['l_name']) : '';

            $dcartcustomer['customer_l_name'] = (isset($_POST['l_name'])) ? $buf.' '.strip_tags($_POST['l_name']) : '';
            $dcartcustomer['customer_l_company'] = (isset($_POST['l_firma'])) ? strip_tags($_POST['l_firma']) : '';
            $dcartcustomer['customer_l_strasse'] = (isset($_POST['l_strasse'])) ? strip_tags($_POST['l_strasse']) : '';
            $dcartcustomer['customer_l_plz'] = (isset($_POST['l_plz'])) ? strip_tags($_POST['l_plz']) : '';
            $dcartcustomer['customer_l_land'] = (isset($_POST['l_land'])) ? strip_tags($_POST['l_land']) : '';
            $dcartcustomer['customer_l_ort'] = (isset($_POST['l_ort'])) ? strip_tags($_POST['l_ort']) : '';
            // Set L.adress fag to true;
            if($dcartcustomer['customer_l_name'] != ' '   ||
               $dcartcustomer['customer_l_company'] != '' || 
               $dcartcustomer['customer_l_strasse'] != '' ||
               $dcartcustomer['customer_l_plz'] != ''     ||
               $dcartcustomer['customer_l_ort'] != '')
            {
                $dcartcustomer['l_adress'] = true;
            }
        }    
        $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();
        if($cart->itemcount <= 0)
            return;
        // Validate prices
        require_once('dominion-jcart/dominion-price-validate.php');
        $valid_prices = validatePrices($cart->get_contents());

        if ($valid_prices !== true) {
		    return 'Cart received incorrect values from client system. Please retry else contact support.<br />
                    Thanks for your patiences.';
        } else if ($valid_prices === true) {
        //echo 'hat geklappt<br />';
            //require_once('dominion-jcart/transaction_control.php');
            //$ordernumber = dcart_createOrder($dcartcustomer,$cart);
            //if ($ordernumber !== false) {
                //print_r($_POST);
            if (isset($_POST['CheckSave'])) {
                global $slipnumber;
                include $settings['file']['customer-mail-data-control.php'];
                if(true !== je_cartCheckUserData()) {
                    $alink = '<a href="'.$_SERVER['HTTP_REFERER'].'">Zurück</a>'; 
                    //header("Location: ".$_SERVER['REDIRECT_REDIRECT_SCRIPT_URI']."?final_gw=1&err=1");
                    return '<script type="text/javascript">'."\n".
                           'alert("Fehler beim Erfassen von Kundendaten,\nbitte überprüfen Sie Ihre eingegebenen Daten '.
                           'auf mögliche Tippfehler."); window.location="'.$_SERVER['HTTP_REFERER'].'";</script>'."\n".
                           '<noscript><strong>Eingabefehler:</strong> '.
                           ' Bitte überprüfen Sie Ihre eingegebenen Daten '.
                           'auf mögliche Tippfehler. '.$alink.'</noscript>';
                }
                include $settings['file']['jcart-data-display.php'];
                return je_displayCustomData();
                    /*
                    include GSPLUGINPATH.'dominion-jcart/jcart-gateway-mail.php';
                    return getDominionPaymentPage($dominion_jcart_path,$dominion_jcart_cat_file,$dcartcustomer,$cart,$ordernumber);
                    */

                }/* else if (isset($_POST['jcart_paypal_checkout'])) {
                include GSPLUGINPATH.'dominion-jcart/jcart-gateway-paypal.php';
                return getDominionPaymentPage($dominion_jcart_path,$dominion_jcart_cat_file,$dcartcustomer,$cart,$ordernumber);
                }   else if (isset($_POST['jcart_webmoney_checkout'])) {
                include GSPLUGINPATH.'dominion-jcart/jcart-gateway-webmoney.php';
                return getDominionPaymentPage($dominion_jcart_path,$dominion_jcart_cat_file,$dcartcustomer,$cart,$ordernumber);
                }*/
                   
            }    
    
    } if (isset($_GET['final_gw']) && $_GET['final_gw'] == '1' && 
         (!isset($_POST['jcart_update_cart']) && !isset($_POST['jcart_empty']))) {
        $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();  
        if($cart->itemcount <= 0)
            return;
      
      include $settings['file']['dominion-customer-info.php'];
      return getDominionCustomerInfoPage();
    } else if(isset($_POST['jcart_update_cart']) || isset($_POST['jcart_empty'])) {
     
        $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();
        $cart->update_cart();
        if(isset($_POST['jcart_update_cart'])) {
            $cart->update_cart();
        } else if(isset($_POST['jcart_empty'])) {
            $cart->empty_cart();
        }

        if(isset($_SERVER['DBENTRY_HOST']) || $_SERVER['DBENTRY_HOST'] != '')
            $s_host = 'http://'.$_SERVER['DBENTRY_HOST'];
        else
            $s_host = dirname(dirname($_SERVER['HTTP_REFERER']));

        // Das hier bitte noch ändern !
        //echo '<p>Produktanzahl geändert! Seite aktualisieren <a href="'.$s_host.'/cart/?dominion_ischeckout=1" >Ok</a></P>'; 
        header( "Location: $s_host"."/cart/?dominion_ischeckout=1");
    } else {
        // Check if cart is empty
        $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();
        // Muss das hier sein?
        if($cart->itemcount <= 0 && (!isset($_GET['id']) && $_GET['id'] != 'sammlung'))
            return;
     
        include $settings['file']['dominion-base.php']; 
        return getDominionCartPage();
    }
}
?>
