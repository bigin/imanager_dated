<?php
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/
if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

/**
* This function creates a directory with the given path name, 
*/
function createFolder($folder, $file, $file_contents)
{
    
    if(file_exists($folder.$file))
        return true;
    if(!mkdir($folder, 0755))
        return false;
    if(!$handle = fopen($folder.$file, 'w'))
        return false;
    fwrite($handle, $file_contents);
    fclose($handle);
    return true;
}


function im_get_posts($all=false) 
{
	$now = time();
	$posts = array();
	$data = @getXML(NMPOSTCACHE);
	foreach ($data->item as $item) 
	{
		if ($all || $item->private != 'Y' && strtotime($item->date) < $now)
		{
			$posts[] = $item;
		}
	}
	return $posts;
}

function im_customfield_def()
{
    $im_customfield_def = null;

	if($im_customfield_def != null)
       return $im_customfield_def;
	
	$files = GSDATAOTHERPATH.IM_CUSTOMFIELDS_FILE;

	if(!file_exists($files)) 
        return '';
	$data = getXML($files);
	$items = $data->item;
	if (count($items) <= 0) 
	    return '';

	foreach($items as $item) 
	{
		$cf = array();
		$cf['key']   = (string)$item->desc;
		$cf['label'] = (string)$item->label;
		$cf['type']  = (string)$item->type;
		$cf['value'] = (string)$item->value;
		if($cf['type'] == 'dropdown') 
		{
		    $cf['options'] = array();
			foreach ($item->option as $option) 
				$cf['options'][] = (string)$option;
		}
		$im_customfield_def[] = $cf;
	}
    return $im_customfield_def;
}


/** these functions should be in the GetSimple Core: */

if (!function_exists('i18n_merge')) 
{
	function i18n_merge($plugin, $language=null) 
	{
		global $i18n, $LANG;
		return i18n_merge_impl($plugin, $language ? $language : $LANG, $i18n);
	}

	function i18n_merge_impl($plugin, $lang, &$globali18n) 
	{ 
		$i18n = array();
		if (!file_exists(GSPLUGINPATH.'items'.'/lang/'.$lang.'.php')) return false;
		@include(GSPLUGINPATH.'items'.'/lang/'.$lang.'.php'); 
		if (count($i18n) > 0) foreach ($i18n as $code => $text) 
		{
			if (!array_key_exists($plugin.'/'.$code, $globali18n)) 
			{
				$globali18n[$plugin.'/'.$code] = $text;
			}
		}
		return true;
	}
}

/** GetSimple 3.0 function - compatibility */

if (!function_exists('i18n')) 
{
	function i18n($name, $echo=true) 
	{
		global $i18n, $LANG;
		if (array_key_exists($name, $i18n)) 
		{
			$myVar = $i18n[$name];
		} 
		else 
		{
			$myVar = '{'.$name.'}';
		}
		if ($echo) 
		{
			echo $myVar; 
		}
		else 
		{
			return $myVar;
		}
	} 
}

/** returns current uri when inside admin panel */
if (!function_exists('return_page_slug'))
{
	function return_page_slug()
    { 
	    return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }
}

/** returns current url */
function curPageURL() 
{
    $isHTTPS = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
    $port = (isset($_SERVER['SERVER_PORT']) && ((!$isHTTPS && $_SERVER['SERVER_PORT'] != '80') || ($isHTTPS && $_SERVER['SERVER_PORT'] != '443')));
    $port = ($port) ? ':'.$_SERVER['SERVER_PORT'] : '';
    return ($isHTTPS ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}
/** Vim bug */
define('XMLTAG', '<?xml version="1.0" encoding="UTF-8" ?>');
?>
