<?php 
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/
class ItemsManager
{
    private $ImReporter;
	public function __construct()
	{
        $this->ImReporter = new IMreporter;
		//Path for uploaded images/files to be placed
		$end_path = GSDATAUPLOADPATH.'items';
        // customfields file
        $custom_fields_file = GSDATAOTHERPATH.IM_CUSTOMFIELDS_FILE; 

		// Alert admin if items manager settings XML file directory does not exist
        if (!file_exists(ITEMDATA)) 
            if(!createFolder(GSDATAPATH.'items', '/.htaccess', 'Deny from all'))
                echo $this->ImReporter->getClause('items/items_path_exists', 
                                                  array('itemmanager-title' => IMTITLE, 
                                                        'gsdatapath' => GSDATAPATH));
            else
                echo $this->ImReporter->getClause('items/directory_succesfull_created', 
                                                  array('end_path' => ITEMDATA));
        
        if(!file_exists($end_path))
            if(!createFolder($end_path, '/.htaccess', 'Allow from all'))
                echo $this->ImReporter->getClause('items/upload_path_exists', 
                                                  array('itemmanager-title' => IMTITLE, 
                                                        'end_path' => $end_path));
            else
                echo $this->ImReporter->getClause('items/directory_succesfull_created', 
                                                  array('end_path' => $end_path));
        
        // Check custom fields file exists
        if(!file_exists($custom_fields_file))
        {
             if(!$handle = fopen($custom_fields_file, 'w')) 
             {
                echo $this->ImReporter->getClause('items/other_path_exists', 
                                                  array('itemmanager-title' => IMTITLE, 
                                                        'end_path' => $custom_fields_file));
             } else {
                $file_contents = '<?xml version="1.0" encoding="UTF-8"?><channel><item></item></channel>';

                fwrite($handle, $file_contents);
                fclose($handle);
                echo $this->ImReporter->getClause('items/file_succesfull_created', 
                                                  array('end_path' => $custom_fields_file));
             }
        }

		if(!file_exists(ITEMDATAFILE))
			$this->processImSettings();
	}
	
    // item files back
	public function getItemsAdmin()
	{
		$items = array();
		$files = getFiles(ITEMDATA);
		foreach ($files as $file) 
		{
			if (is_file(ITEMDATA . $file) && preg_match("/.xml$/", $file)) 
			{
				$items[] = $file;
			}
		}
		sort($items);
		return array_reverse($items);
	}
	

    // Saving the item data
	public function processItem()
	{   
        // Uploader part:
        $err = '';
        $cf_params_array = im_customfield_def();
        if(!isset($_POST['post-title']) || empty($_POST['post-title']))
        {
            echo  '<div class="error">'.$this->ImReporter->getClause('items/err_required_field', 
                    array('fieldname' => $this->ImReporter->getClause('items/title'))).'</div>';
            return false;
        }
        // grab field names (only uploader)
        foreach($cf_params_array as $item)
            if($item['type'] == 'uploader')
            {
                $fieldname = 'post-'.$item['key'];
                $imgname = basename($_POST[$fieldname]);
                // explode temporaire image name
                if(strpos($imgname, '_') !== false && 
                   dirname($_POST[$fieldname]).'/' != ITEMUPLOADPATH)
                {
                    list($id, $datetime, $stdname) = explode('_', $imgname, 3);
                    // copy image to right directory (change "nondynamic" part)
                    $oldfile = GSPLUGINPATH.'items/uploadscript/tmp/'.$imgname;
                    $newfile = ITEMUPLOADPATH.$stdname;
                    // Checks for orphan files & delete them
                    if(!file_exists($newfile))
                        if (!copy($oldfile, $newfile))
                            $err .= $this->ImReporter->getClause('items/err_copy_fail', array('old_file' => $oldfile));
                        else
                            $_POST[$fieldname] = $newfile;
                    else
                        if($this->isFileDead($item['key'], $stdname))
                            if(!copy($oldfile, $newfile))
                                $err .= $this->ImReporter->getClause('items/err_copy_fail', array('old_file' => $oldfile));
                            else
                                $_POST[$fieldname] = $newfile;    
                        else
                            $err .= $this->ImReporter->getClause('items/err_file_exists', array('std_name' => $stdname));
                        
                }
                // overwrite with old value or delete file
                if(!empty($err))
                    if($this->getItemValue($_POST['id'], $item['key']) != $_POST[$fieldname])
                        $_POST[$fieldname] = $this->getItemValue($_POST['id'], $item['key']);
                elseif($this->getItemValue($_POST['id'], $item['key']) != $_POST[$fieldname])
                    if(file_exists($this->getItemValue($_POST['id'], $item['key'])))
                        unlink($this->getItemValue($_POST['id'], $item['key']));
            } 

		$id = clean_urls(to7bits($_POST['post-title']));
        $title = addslashes(htmlspecialchars($_POST['post-title']));


        $file = ITEMDATA . $id . '.xml';
        $orig_file = isset($_POST['id']) ? ITEMDATA.$_POST['id'].'.xml' : ''.'.xml';
		if(file_exists($orig_file) && $id != $_POST['id'])
			unlink($orig_file);


		$category = isset($_POST['category']) ? $_POST['category'] : '';
        $page = isset($_POST['page']) ? $_POST['page'] : '';
        
		$content = safe_slash_htmll($_POST['post-content'] = isset($_POST['post-content']) 
                                    ? $_POST['post-content'] : '');  

		if (!file_exists($file)) 
		{
			$date = date('j M Y');
		} else 
		{
			$data = @getXML($file);
			$date = $data->date;
		}
        
		$xml = @new SimpleXMLExtended(XMLTAG.'<item></item>');
		$xml->addChild('title', empty($title) ? '(no title)' : $title);
		$xml->addChild('slug', $id);
		$xml->addChild('visible', true);
		$xml->addChild('date', $date);
		$xml->addChild('category', $category);
        $xml->addChild('page', $page);
		$note = $xml->addChild('content');  
		$note->addCData($content); 
		$newse = im_customfield_def();

		foreach ($newse as $thes) 
		{
			$keys = $thes['key'];
			if(isset($_POST['post-'.$keys])) 		
			{
				if($keys != 'content' && $keys != 'excerpt')			
				{	
					$tmp = $xml->addChild($keys);
					$tmp->addCData($_POST['post-'.$keys]);
				}
			}
		}
		XMLsave($xml, $file);

		if (!is_writable($file))
            $err .= $this->ImReporter->getClause('items/err_unable_to_write', array('data_file' => IMTITLE));
        if(!empty($err))
			echo '<div class="error">'.$err.'</div>';
		else
            echo $this->ImReporter->getClause('items/succesfull_saved', array('data_file' => IMTITLE));
	}
	
    // This function returns item parameter specified by item id 
    // or item parameter array when no $key parameter is set.    
    private static function getItemValue($item_id, $key = '')
    { 
        $itemfile = ITEMDATA . $item_id . '.xml';
		if (!file_exists($itemfile))
			return false;
		
		$data = @getXML($itemfile);
        if(!empty($key))
            return $data->$key;
        // return array
        return $data;
    }

	public function switchVisibleItem($id)
	{
		$file = ITEMDATA . $id . '.xml';
		if (!file_exists($file))
		{
			echo $this->ImReporter->getClause('items/err_file_not_exist');
            return false;
		}
		
		$data = @getXML($file);
		if (!isset($data->visible) || $data->visible == false)
		{
			$data->visible = true;
			$action = 'action_unhidde';
		} else
		{
			$data->visible = false;
			$action = 'action_hidde';
		}
		XMLsave($data, $file);

		if (!is_writable($file))
            echo '<div class="error">'.$this->ImReporter->getClause('items/err_unable_to_write', 
                                                                    array('data_file' => IMTITLE)).'</div>';
		else
		    echo $this->ImReporter->getClause('items/succesfull_hidde', 
                                              array('data_file' => IMTITLE,
                                              'action' => $this->ImReporter->getClause('items/'.$action)));
		return true;
	}
	
	public function switchPromotedItem($id)
	{
		$file = ITEMDATA . $id . '.xml';
		if (!file_exists($file))
		{
			echo $this->ImReporter->getClause('items/err_file_not_exist');
            return false;
		}
	
		$data = @getXML($file);
		if (!isset($data->promo) || $data->promo == false)
		{
			$data->promo = true;
			$action = 'action_promoted';
		} else
		{
			$data->promo = false;
			$action = 'action_unpromoted';
		}
		XMLsave($data, $file);
		 
		if (!is_writable($file))
            echo '<div class="error">'.$this->ImReporter->getClause('items/err_unable_to_write', 
                                                                    array('data_file' => IMTITLE)).'</div>';
		else 
			echo $this->ImReporter->getClause('items/succesfull_hidde', 
                                              array('data_file' => IMTITLE,
                                              'action' => $this->ImReporter->getClause('items/'.$action)));
		
			return true;
	}
	
	public function deleteItem($id)
	{
		$file = ITEMDATA . $id . '.xml';

        $itemdata = $this->getItemValue($id);
        if(!$itemdata)
            return false;

        $cf_params_array = im_customfield_def();

        foreach($cf_params_array as $item)
            if($item['type'] == 'uploader')
                if(file_exists($itemdata->$item['key']))
                    unlink($itemdata->$item['key']);
                
		if (file_exists($file))
			unlink($file);
		if (file_exists($file))
            echo $this->ImReporter->getClause('items/err_unable_to_delete', array('item_file' => dirname($file)));
		else
			echo $this->ImReporter->getClause('items/item_deleted', array('item_file' => dirname($file)));
        
        return true;
	}
	
    // Rename all item fields
    public function processRenameItems()
    {
        // get item array
        $item_pages = glob(ITEMDATA.'*.xml');
        $allItemsCount = count($item_pages);
        if(!isset($_POST['oldname']) || empty($_POST['oldname']))
            return false;
        if(!isset($_POST['newname']) || empty($_POST['newname']))
            return false;
        
        $needle = htmlspecialchars(stripslashes($_POST['oldname']), ENT_QUOTES);
        $repl = htmlspecialchars(stripslashes($_POST['newname']), ENT_QUOTES);

        if(strlen($repl) >= 100)
            return false;

        $i = 0;
        foreach($item_pages as $page)
        {
            file_put_contents($page, str_replace('<'.$needle.'>', '<'.$repl.'>', file_get_contents($page)));
            file_put_contents($page, str_replace('</'.$needle.'>', '</'.$repl.'>', file_get_contents($page)));
        }

        return true;
    }

    // Category edit
	public function processImSettings()
	{
		$category_file = getXML(ITEMDATAFILE);
		
		//Item Title
		if(isset($_POST['item-title']))
		{
			$file_title = $_POST['item-title'];
		}
		elseif(isset($category_file->item->title))
		{
			$file_title = $category_file->item->title;
		}
		else
		{
			$file_title = IMTITLE;
		}

        // filter
        if(isset($_POST['filter_by_id']))
			$filter_by_id = 1;
		else 
            $filter_by_id = 0;
        
        // sort items by  
        $sort_by = isset($_POST['sortby']) ? $_POST['sortby'] : 'title';

        // items per page 
        $items_per_page = isset($_POST['itemsperpage']) ? $_POST['itemsperpage'] : 10;
        
        /* BACK-END SETTINGS */
        if(isset($_POST['b_filter_by_id']))
			$b_filter_by_id = 1;
		else 
            $b_filter_by_id = 0;
        
        // sort items by  
        $bsort_by = isset($_POST['bsortby']) ? $_POST['bsortby'] : 'title';

        // items per page 
        $bitems_per_page = isset($_POST['bitemsperpage']) ? $_POST['bitemsperpage'] : 30;

        // maximum thumb width properties
        $thumbwidth = isset($_POST['thumbwidth']) ? $_POST['thumbwidth'] : 200;



		$xml = new SimpleXMLExtended(XMLTAG.'<channel></channel>');
		
		$item_xml = $xml->addChild('item');
			
		//Set Title Variable And And Write To XML FIle
		$item_xml->addChild('title', $file_title);
			
        //Set filter by ID
		$item_xml->addChild('filterbyid', $filter_by_id);

        //Set items per page
        $item_xml->addChild('itemsperpage', $items_per_page);

        //Set sort items by
        $item_xml->addChild('sortby', $sort_by);
	
        /* BACK-END */
        //Set filter by ID
		$item_xml->addChild('bfilterbyid', $b_filter_by_id);

        //Set items per page
        $item_xml->addChild('bitemsperpage', $bitems_per_page);

        //Set max thumb width prop 
        $item_xml->addChild('thumbwidth', $thumbwidth);

        //Set sort items by
        $item_xml->addChild('bsortby', $bsort_by);

		//Add Categories
		$category = $xml->addChild('categories');
		if(file_exists(ITEMDATAFILE))
		{		
			foreach($category_file->categories->category as $the_fed)
			{
				$category_uri = $the_fed;
				if(isset($_GET['deletecategory']) && $category_uri == $_GET['deletecategory'])
				{
					
				}
				else
					$category->addChild('category', $category_uri);
			}
		}
		if(isset($_POST['new_category']) && $_POST['new_category'] != "")
            $category->addChild('category', $_POST['new_category']);
			
		//Save XML File
		XMLsave($xml, ITEMDATAFILE);
	}

    private function isFileDead($key, $name)
    {
        $itempages = glob(ITEMDATA.'*.xml');
        foreach($itempages as $itempage)
            if(basename($this->getItemValue(basename($itempage,'.xml'), $key)) == $name)
                return false;
        return true;
    }
}

//Clean URL For Slug
function clean_urls($text)  {
	$text = strip_tags(lowercase($text));
	$code_entities_match = array(' ?',' ','--','&quot;','!','@','#','$','%',
                                 '^','&','*','(',')','+','{','}','|',':','"',
                                 '<','>','?','[',']','\\',';',"'",',','/',
                                 '*','+','~','`','=','.');
	$code_entities_replace = array('','-','-','','','','','','','','','','','',
                                   '','','','','','','','','','','','','');
	$text = str_replace($code_entities_match, $code_entities_replace, $text);
	$text = urlencode($text);
	$text = str_replace('--','-',$text);
	$text = rtrim($text, "-");
	return $text;
}

function to7bits($text,$from_enc='UTF-8') {
	if (function_exists('mb_convert_encoding')) {
		$text = mb_convert_encoding($text,'HTML-ENTITIES',$from_enc);
	}
	$text = preg_replace(
	array('/&szlig;/','/&(..)lig;/','/&([aouAOU])uml;/','/&(.)[^;]*;/'),array('ss',"$1","$1".'e',"$1"),$text);
	return $text;
}

//Function To Clean Posted Content
function safe_slash_htmll($text) {
	if (get_magic_quotes_gpc()==0) 
	{		
		$text = addslashes(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
	} else 
	{		
		$text = htmlentities($text, ENT_QUOTES, 'UTF-8');	
	}
	return $text;
}
?>
