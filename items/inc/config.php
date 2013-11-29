<?php

/* Start the logic part */

// store HTML templates

$Reporter->langRestubsTpl('configure.menu.init.edit.tpl');
// load wrapper tpl
$conf_menu_tpl = catSelector($Reporter).$Reporter->tpls['configure.menu.init.edit.tpl'];

$Reporter->langRestubsTpl('configure.menu.element.init.edit.tpl');
// load element tpl
$conf_menu_ele_tpl = $Reporter->tpls['configure.menu.element.init.edit.tpl'];

$Reporter->langRestubsTpl('configure.menu.js.element.init.edit.tpl');
// load js dyn element tpl
$conf_menu_js_tpl = $Reporter->tpls['configure.menu.js.element.init.edit.tpl'];

// message
$msg = '';
$defs = array();

if (isset($_GET['undo'])) {
    if (items_customfields_undo()) 
    {
        $msg .= $Reporter->getClause('items/undo_success');
        $success = true;
    } else 
    {
        $msg .= $Reporter->getClause('items/undo_failure');
    }
} else if(isset($_POST['save'])) 
{
    $names = items_customfields_invalid_name();
    if(!$names && items_customfields_save_them($msg)) 
    {
        $msg .= $Reporter->getClause('items/save_success').' <a href="load.php?id=items&undo">' . $Reporter->getClause('items/undo') . '</a>';
        $success = true;
    } else {
        if ($names) {
            $msg .= $Reporter->getClause('items/save_invalid').' '.implode(', ', $names);
        } else {
            $msg .= $Reporter->getClause('items/save_failure');
        }
        
        for ($i=0; isset($_POST['cf_'.$i.'_key']); $i++) {
            $cf = array();
            $cf['key'] = htmlspecialchars(stripslashes($_POST['cf_'.$i.'_key']), ENT_QUOTES);
            $cf['label'] = htmlspecialchars(stripslashes($_POST['cf_'.$i.'_label']), ENT_QUOTES);
            $cf['type'] = htmlspecialchars(stripslashes($_POST['cf_'.$i.'_type']), ENT_QUOTES);
            $cf['value'] = htmlspecialchars(stripslashes($_POST['cf_'.$i.'_value']), ENT_QUOTES);
            $cf['options'] = preg_split("/\r?\n/", rtrim(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_value']), ENT_QUOTES)));
            $defs[] = $cf;
        }
        array_pop($defs); // remove the last hidden line
    }
}

// Check category selected
if(isCatValid())
{
    if(empty($defs))
        $defs = im_customfield_def($_POST['cat']);
    $buf_tpl = $conf_menu_ele_tpl;
    $i = 0;
    if(is_array($defs) && count($defs) > 0)
        foreach($defs as $def)
            $buf_tpl .= items_customfields_confline($i++, $def, $conf_menu_ele_tpl, 'sortable');
    $buf_tpl = items_customfields_confline($i, array(), $buf_tpl, 'hidden');
    $conf_menu_ele_tpl = $buf_tpl;
    $conf_menu_tpl = preg_replace(
        '%\[\[( *)categorie_items( *)\]\]%', 
        $conf_menu_ele_tpl, 
        $conf_menu_tpl
    );
    $conf_menu_tpl = preg_replace(
        '%\[\[( *)cat( *)\]\]%', 
        htmlspecialchars(stripslashes($_POST['cat']), ENT_QUOTES), 
        $conf_menu_tpl
    );
    if(!empty($msg)) 
    {
        $conf_menu_tpl = preg_replace(
            '%\[\[( *)js_element( *)\]\]%', 
            preg_replace('%\[\[( *)msg( *)\]\]%', $msg, $conf_menu_js_tpl), 
            $conf_menu_tpl
        );
    }

    echo preg_replace('%\[\[(.*)\]\]%', '', $conf_menu_tpl);

    showRenameTool($Reporter);

} else 
{
    echo catSelector($Reporter);    
}


function items_customfields_invalid_name() 
{
    $stdfields = array();
    $names = array();
    for ($i=0; isset($_POST['cf_'.$i.'_key']); $i++) 
    {
        if (in_array($_POST['cf_'.$i.'_key'], $stdfields)) 
            $names[] = $_POST['cf_'.$i.'_key'];
    }
    return count($names) > 0 ? $names : null;
}


function items_customfields_save_them($msg) 
{
    if(!isCatValid())
    {
        $msg .= $Reporter->getClause('items/invalid_category').'<br />';
        return false;
    }
    if(!customfields_file(GSDATAOTHERPATH.htmlspecialchars(stripslashes($_POST['cat']), ENT_QUOTES)
       .'_'.IM_CUSTOMFIELDS_FILE, ENT_QUOTES))
           return false;
    if(!copy(GSDATAOTHERPATH.htmlspecialchars(stripslashes($_POST['cat']), ENT_QUOTES).'_'.IM_CUSTOMFIELDS_FILE, 
       GSBACKUPSPATH.'other/'.htmlspecialchars(stripslashes($_POST['cat']), ENT_QUOTES).'_'.IM_CUSTOMFIELDS_FILE))
        return false;

 	$data = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
    for($i=0; isset($_POST['cf_'.$i.'_key']); $i++) {
        if ($_POST['cf_'.$i.'_key']) {
            $item = $data->addChild('item');
            $item->addChild('desc')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_key']), ENT_QUOTES));
            $item->addChild('label')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_label']), ENT_QUOTES));
            $item->addChild('type')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_type']), ENT_QUOTES));
            if($_POST['cf_'.$i.'_value']) {
                $item->addChild('value')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_value']), ENT_QUOTES));
            }
            if ($_POST['cf_'.$i.'_options']) {
                $options = preg_split("/\r?\n/", rtrim(stripslashes($_POST['cf_'.$i.'_options'])));
                foreach ($options as $option) {
                    $item->addChild('option')->addCData(htmlspecialchars($option, ENT_QUOTES));
                }
            }
        }
    }
 	XMLsave($data, GSDATAOTHERPATH.htmlspecialchars(stripslashes($_POST['cat']), ENT_QUOTES).'_'.IM_CUSTOMFIELDS_FILE);
    return true;
}

function items_customfields_undo() 
{
    return copy(GSBACKUPSPATH.'other/'.htmlspecialchars(stripslashes($_POST['cat']), ENT_QUOTES).'_'.IM_CUSTOMFIELDS_FILE, 
                GSDATAOTHERPATH.htmlspecialchars(stripslashes($_POST['cat']), ENT_QUOTES).'_'.IM_CUSTOMFIELDS_FILE);
}

function customfields_file($file)
{
    // Check custom fields file exists
    if(file_exists($file))
        return true;
    if(!$handle = fopen($file, 'w')) 
        return false;
            
    $file_contents = XMLTAG.'<channel><item></item></channel>';
    fwrite($handle, $file_contents);
    fclose($handle);
    return true;
}


function items_customfields_confline($i, $def, $tpl, $class = '')
{

    $isdropdown = @$def['type'] == 'dropdown';
    $options = "\r\n";
    if ($isdropdown && count($def['options']) > 0) 
    {
        foreach ($def['options'] as $option) 
            $options .= $option . "\r\n";

        $tpl = preg_replace(
            '%\[\[( *)area-options( *)\]\]%', 
            $options, 
            $tpl
        );

    }
    $tpl = preg_replace(
        '%\[\[( *)tr-class( *)\]\]%', 
        $class, 
        $tpl
    );
    $tpl = preg_replace(
        '%\[\[( *)i( *)\]\]%', 
        $i, 
        $tpl
    );
    $tpl = preg_replace(
        '%\[\[( *)key( *)\]\]%', 
        @$def['key'], 
        $tpl
    );
    $tpl = preg_replace(
        '%\[\[( *)label( *)\]\]%', 
        @$def['label'], 
        $tpl
    );
    // text 
    $tpl = preg_replace(
        '%\[\[( *)selected-text( *)\]\]%', 
        @$def['type']=='text' ? 'selected="selected"' : '', 
        $tpl
    );
    // textarea
    $tpl = preg_replace(
        '%\[\[( *)selected-longtext( *)\]\]%', 
        @$def['type']=='textfull' ? 'selected="selected"' : '', 
        $tpl
    );
    // dropdown
    $tpl = preg_replace(
        '%\[\[( *)selected-dropdown( *)\]\]%', 
        @$def['type']=='dropdown' ? 'selected="selected"' : '', 
        $tpl
    );
    // checkbox
    $tpl = preg_replace(
        '%\[\[( *)selected-checkbox( *)\]\]%', 
        @$def['type']=='checkbox' ? 'selected="selected"' : '', 
        $tpl
    );
    // editor
    $tpl = preg_replace(
        '%\[\[( *)selected-editor( *)\]\]%', 
        @$def['type']=='textarea' ? 'selected="selected"' : '', 
        $tpl
    );
    // hidden
    $tpl = preg_replace(
        '%\[\[( *)selected-hidden( *)\]\]%', 
        @$def['type']=='hidden' ? 'selected="selected"' : '', 
        $tpl
    );
    // uploader
    $tpl = preg_replace(
        '%\[\[( *)selected-file( *)\]\]%',
        @$def['type']=='uploader' ? 'selected="selected"' : '', 
        $tpl
    );

    $tpl = preg_replace(
        '%\[\[( *)area-display( *)\]\]%', 
        !$isdropdown ? 'display:none' : '', 
        $tpl
    );
    $tpl = preg_replace(
        '%\[\[( *)text-options( *)\]\]%', 
        @$def['value'], 
        $tpl
    );

    return $tpl;
}

