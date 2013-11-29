<?php 
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin 
* Author URI: http://www.ehret-studio.de
*/
class ImModel
{
    private $is_admin_panel;
    private $items_raw_struct = array();
    private $items_spec_keys = array();
    private $sort_by;
    private $item_in_cat = array();
    private $imcat;
    private $imfcon;
    /* our static resources */
    private static $properties;
    private static $preferences;
    private static $input;
    /** 
     * register of: 
     * key = procedure,value = tpl */
    private static $tpls = array(
        'output' => 'output.admin.tpl',
        'msg' => '',
        'catselector' => array(
            'selector.admin.tpl', 
            'selector.admin.element.tpl'
        ),
        'tabpanel' => 'tabpanel.admin.tpl',
        
        'itemregister' => array(
            'itemlist.admin.tpl', 
            'itemlist.admin.element.tpl', 
            'itemlist.admin.vicon.tpl', 
            'itemlist.admin.picon.tpl'
        ),
        'itemeditor' => array(
            'edititem.admin.tpl',
            'edititem.admin.option.tpl', 
            'edititem.admin.wrapper.tpl', 
            'edititem.admin.wrapper.element.tpl', 
            'edititem.admin.wrapper.hidden.tpl', 
            'edititem.admin.wrapper.input.tpl',
            'edititem.admin.dropdown.wrapper.tpl', 
            'edititem.admin.dropdown.tpl', 
            'edititem.admin.checkbox.tpl',
            'edititem.admin.area.tpl', 
            'edititem.admin.hidden.tpl', 
            'edititem.admin.thumb.tpl', 
            'edititem.admin.wrapper.js.tpl'
        ),
        'paginator' => array(
            'paginator.tpl', 
            'paginator.prev.tpl', 
            'paginator.next.tpl',
            'paginator.centre.tpl', 
            'paginator.prev.inactive.tpl', 
            'paginator.next.inactive.tpl',
            'paginator.centre.inactive.tpl', 
            'paginator.ellipsis.tpl', 
            'paginator.secondlast.tpl',
            'paginator.last.tpl', 
            'paginator.first.tpl', 
            'paginator.second.tpl'
        ),
        'categoryregister' => array(
            'categorylist.admin.tpl', 
            'categorylist.admin.element.tpl', 
            'categorylist.admin.rowheader.tpl'
        ),
        'fieldsconfigurator' => array(
            'configure.admin.tpl', 
            'configure.admin.element.tpl', 
            'configure.admin.element.js.tpl', 
            'configure.admin.linkundo.tpl'
        ),
        'renametool' => 'renametool.admin.tpl',

        'preferencer' => array(
            'settings.admin.tpl', 
            'edititem.admin.option.tpl'
        )
    );

    public $items_ordered_struct = array();
    public $pagedata = array();
    public $itemdata = array();
    public $fields = null;
    public static $setup;


	public function __construct($input)
	{
        self::$input = $input;
        self::$setup = false;
        global $SITEURL;
        self::$properties['paths'] = array(
                'templatedir' => GSPLUGINPATH.'imanager/tpl/',
                'uploaddir'   => GSDATAUPLOADPATH.'imanager',
                'uploadpart'  => ITEMUPLOADDIR,
                'preferfile'  => ITEMDATAFILE,
                'siteurl'     => $SITEURL,
        );
      
        self::$preferences = getXML(self::$properties['paths']['preferfile']);
        //$this->imrep = new ImReporter(GSPLUGINPATH.'imanager/tpl/');
        $this->imcat = new ImCategory(self::$preferences);
        
        // initialise field configurator
        $this->imfcon = new ImFieldsConfigurator(self::custom_fields_file(), 
                GSBACKUPSPATH.'other/'.ImCategory::$current_category.'.'.IM_CUSTOMFIELDS_FILE);
        // check if user inside admin panel
        $this->is_admin_panel = (!defined('IN_GS')  || 
                strpos($_SERVER ['REQUEST_URI'],'/admin/') === false) ? false : true;

		// Alerts admin if items manager settings XML file directory does not exist
        if (!file_exists(ITEMDATA))
        {
            if(!$this->create_folder_procedure(GSDATAPATH.'imanager', '/.htaccess', 'Deny from all'))
            {
                ImMsgReporter::setClause(
                        'items_path_exists', 
                        array('itemmanager-title' => IMTITLE, 
                        'gsdatapath' => GSDATAPATH)
                );
            } else 
            {
                ImMsgReporter::setClause(
                        'directory_succesfull_created', 
                        array('end_path' => ITEMDATA)
                );
            }
            self::$setup = true;
        }
   
        if(!file_exists(self::$properties['paths']['uploaddir']))
        {
            if(!$this->create_folder_procedure(self::$properties['paths']['uploaddir'], '/.htaccess', 'Allow from all'))
            {
                ImMsgReporter::setClause('upload_path_exists', 
                        array('itemmanager-title' => IMTITLE, 
                              'end_path' => self::$properties['paths']['uploaddir'])
                );
            } else
            {
                ImMsgReporter::setClause('directory_succesfull_created', 
                        array('end_path' => self::$properties['paths']['uploaddir'])
                );
            }
            self::$setup = true;
        }
		if(!file_exists(self::$properties['paths']['preferfile']))
        {
			    $this->setupconfig();
                self::$setup = true;
        }

        // if categories have not yet been created
        if(!ImCategory::$is_cat_exist && !self::$setup)
        {
            if(isset(self::$input['view'])) 
            {
                ImMsgReporter::setClause('no_items_yet'); 
            } elseif(isset(self::$input['edit']))
            {
                ImMsgReporter::setClause('no_category_created',
                    array('noun' => ImMsgReporter::getClause('elements')));
            } elseif(isset(self::$input['fields']))
            {
                ImMsgReporter::setClause('no_fields_yet');
            }
        }
	}

    /* ~Getter~ */

    /* TEMPLATES */
    public static function getTplKit($key){  
        return self::$tpls[$key]; 
    }
    /* SCRIPT PROPERTIES */
    public static function getProp($key, $ref)
    {
        return self::$properties[$key][$ref];
    }
    /* PROGRAM PREFERENCES */
    public static function getPref()
    {
        return self::$preferences;
    }



    /* ~Setter~ */
    public static function setTplKit($key, $val) 
    {  
        self::$tpls[$key] = $val; 
    }

    public static function setPref($key, $val)
    {  
        self::$preferences->item->$key = $val; 
    }


    /* setup item data and c.filds */
    public function itemdata_setup_restore($id)
    {/*{{{*/
        $file = ITEMDATA . safe_slash_html_input(basename($id)). '.xml';
        $this->itemdata = @getXML($file);
        $this->fields = self::custom_fields();
    }/*}}}*/


    /* setup only c.fields */
    public function cfields_setup_restore()
    {
        $this->fields = self::custom_fields();
    }

     /* This function returns an item parameter specified by item id 
       or item parameter array when the $key parameter isn't set. */    
    public function get_item_data($id, $key='', $showhidden=false)
    { /*{{{*/
        //$itemfile = ITEMDATA . $id . '.xml';
        $file = ITEMDATA . safe_slash_html_input(basename($id)). '.xml';
		if (!file_exists($file))
			return false;

		$data = @getXML($file);
        if(!empty($key))
            return !isset($data->$key) ? false : $data->$key;
        if($showhidden)
            return $data;
        elseif($data->visible == 1)
            return $data;
        return false;
    }/*}}}*/


    /* generate register data */
    public function gen_register($fields_to_display=array(), $node=null)
    {/*{{{*/
        if($node != null)
            $this->sort_by = $node;

        // item files
        $items = $this->item_files();
        // item fields
        $fields = self::custom_fields();
        // keys specified
        if($fields)
            $this->items_spec_keys = self::sorted_keys($fields);
        $i = 0;
        foreach($items as $item)
        {
            $data = getXML($item);
			$visible = isset($data->visible) ? (int)$data->visible : 1;
			$promoted = isset($data->promo) ? (int)$data->promo : 0;
            /* explicit specification of the type is needed 
               otherwise an alert appears by type definition */
			$this->items_raw_struct[$i] = array(
                'key'      => $i,
                'slug'     => (string)$data->slug,
                'title'    => (string)$data->title,
                'page'     => (string)$data->page,
                'date'     => (string)$data->date,
                'name'     => basename($item), 
				'category' => (string)$data->category, 
				'visible'  => (string)$visible, 
				'promo'    => (string)$promoted
            );
            // Array push dynamic values
            if(!empty($fields_to_display) && 
               is_array($fields_to_display)) 
            {
                foreach($fields_to_display as $field)
                {
                    if(isset($this->items_spec_keys['uploader']) && 
                            in_array($field, $this->items_spec_keys['uploader']))
                    {   
                        // urls
                        $this->items_raw_struct[$i][$field] = self::$properties['paths']['siteurl'].ITEMUPLOADDIR.basename((string)$data->{$field});
                    } else
                    {
                        // other properties
                        $this->items_raw_struct[$i][$field] = stripcslashes((string)$data->{$field});
                    }    
                }
            }
            $i++;
        }
        // filtered item results
        $this->items_ordered_struct = $this->item_filter();
        //var_dump($this->items_ordered_struct);
        if(!empty($this->items_ordered_struct))
        {
            $this->pagedata_preprocessor();
            return true;
        }
        if(!self::$setup && !isset(self::$input['category']))
            ImMsgReporter::setClause('no_items');
        return false;

    }/*}}}*/


    /* gives custom fields file of current category*/
    private static function custom_fields_file()
    {
        return GSDATAOTHERPATH.ImCategory::$current_category.'.'.IM_CUSTOMFIELDS_FILE;
    }


    /* gives custom fields for current category */
    private static function custom_fields()
    {/*{{{*/
        $f = array();
	    $file = self::custom_fields_file();
	    if(!file_exists($file)) 
            return false;

	    $data = getXML($file);
	    $items = $data->item;
	    if(count($items) <= 0) 
	        return false;
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
		    $f[] = $cf;
	    }
        return $f;
    }/*}}}*/


    /* start save process custom files */
    public function fieldsgenerator()
    {/*{{{*/
        $q = $this->imfcon->save_preprocessor();
        if($q == 1)
        {
            ImMsgReporter::setClause('save_success');
            return true;
        } elseif($q == 0)
        {
            ImMsgReporter::setClause('save_invalid').' '.implode(', ', $this->imfcon->_invalid_names);
            return false;
        } else
        {
            ImMsgReporter::setClause('save_failure');
            return false;
        }
    }/*}}}*/

    /* returns sorted keys by type */
    private static function sorted_keys($fields)
    {/*{{{*/
        $sorts = array();
        foreach($fields as $field)
        {
            switch($field['type'])
            {
                case 'uploader':
                    $sorts['uploader'][] = $field['key'];
                    break;
                case 'dropdown':
                    $sorts['dropdown'][] = $field['key'];
                    break;
                case 'text':
                    $sorts['text'][] = $field['key'];
                    break;
                case 'textfull':
                    $sorts['textfull'][] = $field['key'];
                    break;
                case 'checkbox':
                    $sorts['checkbox'][] = $field['key'];
                    break;
                case 'textarea':
                    $sorts['textarea'][] = $field['key'];
                    break;
                case 'hidden':
                    $sorts['hidden'][] = $field['key'];
                    break;
                default:
                    $sorts['undefined'][] = $field['key'];
            }
        }
        return $sorts;
    }/*}}}*/


    /* returns item number within given category */
    public function items_within_cat($cat) 
    {/*{{{*/
        $num = null;
        // run register function
        $this->gen_register();
        $items = $this->item_files();
        foreach($this->items_raw_struct as $item)
        {
            if($cat == $item['category'])
            {
                $this->item_in_cat[] = $item['slug'];
                $num[] = $item['category'];
            }
        }
        return count($num);
    }/*}}}*/


    private function item_filter()
	{/*{{{*/
        // check sort by settings front-end / back-end 
        if(is_null($this->sort_by))
            if(!$this->is_admin_panel)
                $this->sort_by = !empty(self::$preferences->item->sortby) ? 
                    (string)self::$preferences->item->sortby : 'title';
            else
                $this->sort_by = !empty(self::$preferences->item->bsortby) ? 
                    (string)self::$preferences->item->bsortby : 'title';

		$packs = array();
		$i = 0;
        foreach($this->items_raw_struct as $element)
        {
            //var_dump($element);
            // filter by visible only front-end
            if($element['visible'] == false 
               && (!$this->is_admin_panel))
                continue;

            // filter items by category
            if($element['category'] != ImCategory::$current_category)
                continue;

            $packs[$i] = $element;
            if(!empty($element[$this->sort_by]))
			    if(is_numeric($element[$this->sort_by]))
				    $packs[$i][$this->sort_by] = (int)$element[$this->sort_by];
				else
				    $packs[$i][$this->sort_by] = (string)$element[$this->sort_by];
                $i++;
		}

        asort($packs);
		usort($packs, array($this, 'sort_array'));
		return $packs;
	}/*}}}*/


	/** 
	* 
	* @param $a $b array the data to be sorted (from usort)
	* @return bool
	*/  
	private function sort_array($a, $b)
	{/*{{{*/
       	$a = $a[$this->sort_by];
       	$b = $b[$this->sort_by];
		if(is_numeric($a))
		{
			if ($a == $b) 
			{ 
				return 0; 
			} 
			else
			{  
				if($a<$b) 
				{ 
					return 1; 
				} 
				else 
				{ 
					return -1; 
				} 
			} 
		}
		else
		{
			 return strcmp($a, $b);
		}
	}/*}}}*/

	
   /* returns item files array */
	private function item_files()
	{/*{{{*/
		$items = array();
		$items = glob(ITEMDATA.'*.xml');
		sort($items);
		return array_reverse($items);
	}/*}}}*/


    /* returns itam ids array */
    private function item_ids()
    {/*{{{*/
		$items = array();
        $ids = array(); 
		$items = glob(ITEMDATA.'*.xml');
        foreach($items as $item)
            $ids[] = basename($item, '.xml');
		sort($ids);
		return array_reverse($ids);
	}/*}}}*/

        
    /* handles data to display on the pages */
    private function pagedata_preprocessor()
    {/*{{{*/
        $items_st = $this->items_ordered_struct;
        // the number of items per page
        $this->pagedata['limit'] = isset(self::$preferences->item->itemsperpage) 
            ? self::$preferences->item->itemsperpage : 10;
        if($this->is_admin_panel)
            $this->pagedata['limit'] = isset(self::$preferences->item->bitemsperpage) 
                ? self::$preferences->item->bitemsperpage : 10;
        $this->pagedata['viewpage'] = isset(self::$preferences->item->page) 
            ? self::$preferences->item->page : '';
        // How many adjacent pages should be shown on each side
        $this->pagedata['adjacents'] = 3;
        // last page
        $this->pagedata['lastpage'] = (int)(ceil(count($items_st) / $this->pagedata['limit']));
        // handle get
        if(isset(self::$input['page']) && self::$input['page'] <= 0)
            self::$input['page'] = 1;
        elseif(isset(self::$input['page']) && self::$input['page'] > $this->pagedata['lastpage'])
            self::$input['page'] = $this->pagedata['lastpage'];
	    $this->pagedata['page'] = !empty(self::$input['page']) ? (int)self::$input['page'] : 1;
        // first page to display
        $this->pagedata['start'] = !empty($this->pagedata['page']) 
            ? (($this->pagedata['page'] - 1) * $this->pagedata['limit']) : 0;
        // next page
        $this->pagedata['next'] = $this->pagedata['page'] + 1;

        // just for counting of rows
        $act_row = $this->pagedata['start'];
        $index = $this->pagedata['start'] + $this->pagedata['limit'];

        // active item keys
        while(isset($items_st[$act_row]) && $act_row < $index)
        {
            $this->pagedata['itemkeys'][] = $act_row;
            $act_row++;
        }

        // initialize jquery id
        $this->pagedata['jid'] = '';
        if(isset(self::$input['delete']))
            $this->pagedata['jid'] = safe_slash_html_input(self::$input['delete']);
        elseif(isset(self::$input['promo']))
            $this->pagedata['jid'] = safe_slash_html_input(self::$input['promo']);
        elseif(isset(self::$input['visible']))
            $this->pagedata['jid'] = safe_slash_html_input(self::$input['visible']);
        
        // Setup page vars to display.
	    $this->pagedata['prev'] = $this->pagedata['page']     - 1;
	    //$this->pagedata['next'] = $this->pagedata['page']     + 1;
        $this->pagedata['lpm1'] = $this->pagedata['lastpage'] - 1;

        // fixing url inside admin: remove redundant 'page' param
        // todo: search engine friendly URL 
        $this->pagedata['pageurl'] = self::$properties['paths']['siteurl'].return_page_slug().'/?page=';
        if($this->is_admin_panel)
            if(strpos(curPageURL(),'&page=')!==false)
                $this->pagedata['pageurl'] = reparse_url(parse_url(curPageURL()));
            else
                $this->pagedata['pageurl'] = curPageURL().'&cat='.ImCategory::$current_category.'&page=';

    }/*}}}*/
	

    // Saving the item data
	public function saveitem()
	{   /*{{{*/
        // check for required data 
        if(!isset(self::$input['post-title']) || empty(self::$input['post-title']))
             ImMsgReporter::setClause('err_required_field', 
                    array('fieldname' => ImMsgReporter::getClause('title')));
        if(!isset(self::$input['post-category']) || empty(self::$input['post-category']))
            ImMsgReporter::setClause('err_required_field', 
                    array('fieldname' => ImMsgReporter::getClause('category')));
        if(!ImCategory::is_cat_valid(self::$input['post-category']))
            ImMsgReporter::setClause('invalid_category', 
                    array('fieldname' => ImMsgReporter::getClause('category')));
        $msg = ImMsgReporter::msgs();
        if(!empty($msg))
            return false;
        if(!ImCategory::setCategory(self::$input['post-category']))
            return false;
        
    
        $fields =  self::custom_fields();

		$id = clean_urls(to7bits(self::$input['post-title']));
        $title = safe_slash_html_input(self::$input['post-title']);
        
        $orig_file = isset(self::$input['id']) ? ITEMDATA.self::$input['id'].'.xml' : ''.'.xml';
		if(file_exists($orig_file) && $id != self::$input['id'])
			unlink($orig_file);

        // validate category
        if(!ImCategory::is_cat_valid(self::$input['post-category']))
        {
            ImMsgReporter::setClause('invalid_category');
            return false;
        }
        $category = safe_slash_html_input(self::$input['post-category']);
        // avoid id duplication within same category (new item only)
        if(!self::$input['edititem'])
        {
            if(in_array($id, $this->item_ids()))
            {
                ImMsgReporter::setClause('err_item_exists', array('std_name' => $title));
                return false;
            }
        }     
        $page = isset(self::$input['post-page']) ? safe_slash_html_input(self::$input['post-page']) : '';
		$content = isset(self::$input['post-content']) ? safe_slash_html_input(self::$input['post-content']) : '';
		if (!$data_date = $this->get_item_data($id, 'date', true))
			$date = date('j M Y');
		else
			$date = $data_date;
        
		$xml = @new SimpleXMLExtended(XMLTAG.'<item></item>');
		$xml->addChild('title', empty($title) ? '(no title)' : $title);
		$xml->addChild('slug', $id);
		$xml->addChild('visible', isset(self::$input['post-visible']) 
                ? (string)self::$input['post-visible'] : 1);
        $xml->addChild('promo', isset(self::$input['post-promo']) 
                ? (string)self::$input['post-promo'] : 0);
		$xml->addChild('date', $date);
		$xml->addChild('category', $category);
        $xml->addChild('page', $page);
		$note = $xml->addChild('content');  
		$note->addCData($content);

        $FILE = ITEMDATA . $id . '.xml';
        
        if(!isset($fields) || !is_array($fields) || count($fields) < 1)
        {
            XMLsave($xml, $FILE);
            if (!is_writable($FILE))
            {
                ImMsgReporter::setClause('err_unable_to_write', array('data_file' => $FILE));
                return false;
            }
            ImMsgReporter::setClause('succesfull_saved', array('data_file' => $FILE));
            return true;
        }

        foreach($fields as $field)
        {
            if($field['type'] == 'uploader')
            {
                $fieldname = 'post-'.$field['key'];
                if(!isset(self::$input[$fieldname]))
                    continue;
                $imgname = basename(self::$input[$fieldname]);
                // explode temporaire image name
                if(strpos($imgname, '_') !== false && 
                   dirname(self::$input[$fieldname]).'/' != ITEMUPLOADPATH)
                {
                    @list($id, $datetime, $stdname) = explode('_', $imgname, 3);
                    // copy image to right directory (todo: change "nondynamic" part)
                    $oldfile = GSPLUGINPATH.'imanager/uploadscript/tmp/'.$imgname;
                    $newfile = ITEMUPLOADPATH.$stdname;
                    // look for orphan files & delete them
                    if(!file_exists($newfile) && file_exists($oldfile)) {
                        if (!copy($oldfile, $newfile)) {
                            ImMsgReporter::setClause('err_copy_fail', array('old_file' => $oldfile));
                        } else {
                            self::$input[$fieldname] = $newfile;
                        } 
                    } elseif(file_exists($oldfile)) {
                        if($this->is_file_dead($field['key'], $stdname)) {
                            if(!copy($oldfile, $newfile)) {
                                ImMsgReporter::setClause('err_copy_fail', array('old_file' => $oldfile));
                            } else {
                                self::$input[$fieldname] = $newfile;
                            }
                        } else {
                            ImMsgReporter::setClause('err_file_exists', array('std_name' => $stdname));
                        }
                    } else {
                        ImMsgReporter::setClause('err_file_removed', array('old_file' => $oldfile));
                    }
                }   
            }
            // overwrite with old value or delete file
            $msg = ImMsgReporter::msgs();
            if(!empty($msg))
                if($this->get_item_data(self::$input['id'], $field['key'], true) != self::$input[$fieldname])
                    self::$input[$fieldname] = $this->get_item_data(self::$input['id'], $field['key'], true);
                elseif($this->get_item_data(self::$input['id'], $field['key'], true) != self::$input[$fieldname])
                    if(file_exists($this->get_item_data(self::$input['id'], $field['key'], true)))
                        unlink($this->get_item_data(self::$input['id'], $field['key'], true));
        
			if(isset(self::$input['post-'.$field['key']])) 		
			{
			    if($field['key'] != 'content' && $field['key'] != 'excerpt')			
				{
					$tmp = $xml->addChild(safe_slash_html_input($field['key']));
					$tmp->addCData(safe_slash_html_input(self::$input['post-'.$field['key']]));
				}
			}
        }
        
        XMLsave($xml, $FILE);
		if (!is_writable($FILE))
        {
            ImMsgReporter::setClause('err_unable_to_write', array('data_file' => basename($FILE)));
            return false;
        }
        ImMsgReporter::setClause('succesfull_saved', array('data_file' => basename($FILE)));
        return true;
    }/*}}}*/


    public function visibility_change()
	{/*{{{*/
		$data = $this->get_item_data(self::$input['visible'], '', true);
		if(!is_object($data))
        {
            ImMsgReporter::setClause('err_unknow_itemid');
            return false;
        }
		if (empty($data->visible) || $data->visible == 0)
		{
			$data->visible = 1;
		} else
		{
			$data->visible = 0;
		}
        //self::$input = $data;
        foreach($data as $key => $val)
        {
            if($key != 'slug')
            {
                self::$input['post-'.$key] = (string)$val;
            } else
            {
                self::$input[$key] = (string)$val;
            }
        }
        self::$input['edititem'] = true;
        if(!$this->saveitem(self::$input))     
            return false;
        return true;
	}/*}}}*/

	
	public function promotion_change()
	{/*{{{*/
        $data = $this->get_item_data(self::$input['promo'], '', true);
        if(!is_object($data))
        {
            ImMsgReporter::setClause('err_unknow_itemid');
            return false;
        }
		if (empty($data->promo) || $data->promo == 0)
		{
			$data->promo = 1;
		} else
		{
			$data->promo = 0;
		}
        //self::$input = $data;
        foreach($data as $key => $val)
        {
            if($key != 'slug')
            {
                self::$input['post-'.$key] = (string)$val;
            } else
            {
                self::$input[$key] = (string)$val;
            }
        }
        self::$input['edititem'] = true;
        if(!$this->saveitem())     
            return false;
        return true;
	}/*}}}*/

	
	public function item_delete($id)
	{/*{{{*/
        $data = $this->get_item_data($id,'', true);
        if(!is_object($data))
        {
            ImMsgReporter::setClause('err_unknow_itemid');
            return false;
        }
        $fields =  self::custom_fields();
        if(is_array($fields) && count($fields) > 0)
            foreach($fields as $field)
                if($field['type'] == 'uploader')
                    if(file_exists($data->$field['key']))
                        unlink($data->$field['key']);
        
        $file = ITEMDATA.safe_slash_html_input(basename($id)). '.xml';   
		if (file_exists($file))
			unlink($file);
		if (file_exists($file))
        {
            ImMsgReporter::setClause('err_unable_to_delete', array('item_file' => basename($file)));
            return false;
        }
		ImMsgReporter::setClause('item_deleted', array('item_file' => basename($file)));
        return true;
	}/*}}}*/
	
    // Rename all item fields
    public function rename_item_fields()
    {
        $this->cfields_setup_restore();
        $this->gen_register();
        if(!isset(self::$input['oldname']) || empty(self::$input['oldname']))
        {
            ImMsgReporter::setClause('err_by_empty_field', array('field' => ImMsgReporter::getClause('searchfor')));
            return false;
        }
        if(!isset(self::$input['newname']) || empty(self::$input['newname']))
        {
            ImMsgReporter::setClause('err_by_empty_field', array('field' => ImMsgReporter::getClause('switchto')));
            return false;
        }
        $blacklist = array('slug','title','page','date','name','category','visible','promo');
        $needle = strtolower(safe_slash_html_input(self::$input['oldname']));
        $repl =  strtolower(safe_slash_html_input(self::$input['newname']));
        if(in_array($needle, $blacklist))
        {
            ImMsgReporter::setClause('err_name_reserved', array('field' => $needle));
            return false;
        }
        if(in_array($repl, $blacklist))
        {
            ImMsgReporter::setClause('err_name_reserved', array('field' => $repl));
            return false;
        }

        $i = 0;
        $cat = '';
        if(ImCategory::is_cat_valid(self::$input['cat']))
            $cat =  safe_slash_html_input(self::$input['cat']);
        foreach($this->items_raw_struct as $item)
        {
            if($cat == $item['category'])
            {
                if(false !== file_put_contents(ITEMDATA.$item['slug'].'.xml', str_replace('<'.$needle.'>', 
                   '<'.$repl.'>', file_get_contents(ITEMDATA.$item['slug'].'.xml'))))
                    $i++;
                file_put_contents(ITEMDATA.$item['slug'].'.xml', str_replace('</'.$needle.'>', 
                   '</'.$repl.'>', file_get_contents(ITEMDATA.$item['slug'].'.xml')));
            }
        }
        if($i > 0)
            ImMsgReporter::setClause('succesfull_fields_updated', array('count' => $i));
        else
            ImMsgReporter::setClause('no_fields_updated');

        return true;
    }

    /* program preferences */
	public function setupconfig()
	{/*{{{*/
        $delcat = isset(self::$input['deletecategory']) ? self::$input['deletecategory'] : false;
		//$prefer_file = getXML(ITEMDATAFILE);
        //self::$preferences;
		//Item Title
        $file_title = IMTITLE;
        // sort items by  
        $sort_by = isset(self::$input['sortby']) ? safe_slash_html_input(self::$input['sortby']) : 'title';
        // items per page 
        $items_per_page = isset(self::$input['itemsperpage']) ? (int) self::$input['itemsperpage'] : 10;
        
        /* BACK-END SETTINGS */        
        // sort items by  
        $bsort_by = isset(self::$input['bsortby']) ? safe_slash_html_input(self::$input['bsortby']) : 'title';
        // items per page 
        $bitems_per_page = isset(self::$input['bitemsperpage']) ? (int) self::$input['bitemsperpage'] : 30;
        // max thumb width
        $thumbwidth = isset(self::$input['thumbwidth']) ? (int) self::$input['thumbwidth'] : 200;

		$xml = new SimpleXMLExtended(XMLTAG.'<channel></channel>');
		$item_xml = $xml->addChild('item');
		//Set Title Variable And And Write To XML FIle
		$item_xml->addChild('title', $file_title);
        //Set items per page
        $item_xml->addChild('itemsperpage', $items_per_page);
        //Set sort items by
        $item_xml->addChild('sortby', $sort_by);
        /* BACK-END */
        //Set items per page
        $item_xml->addChild('bitemsperpage', $bitems_per_page);
        //Set max thumb width prop 
        $item_xml->addChild('thumbwidth', $thumbwidth);
        // set sort items by field
        $item_xml->addChild('bsortby', $bsort_by);
        $pattern = array();
		// add/delete categories
		$category = $xml->addChild('categories');
		if(isset(self::$preferences->categories->category[0])) {
			foreach(self::$preferences->categories->category as $cat) {
                $pattern[] = $cat;
				if(!isset(self::$input['deletecategory']) || 
                   $cat != safe_slash_html_input(self::$input['deletecategory'])) {
					$category->addChild('category', safe_slash_html_input($cat));
                } elseif($this->items_within_cat($cat) > 0) {
                    // delete all items within category
                    foreach($this->item_in_cat as $id)
                        $this->item_delete($id);
                }
            }
        }
        
		if(isset(self::$input['new_category']) && 
           !empty(self::$input['new_category']))
        {
            if(!empty($pattern) && !in_array(self::$input['new_category'], $pattern))
                $category->addChild('category', self::$input['new_category']);
            elseif(empty($pattern))
                $category->addChild('category', self::$input['new_category']);
            else
                ImMsgReporter::setClause(
                    'err_catname_duplication', 
                    array('end_path' => ITEMDATA)
                );
        }
         // delelete custom fields file 
        if($delcat) 
        {
            if(ImCategory::setCategory($delcat))               
                $this->imfcon->unlink_customfiels_file(self::custom_fields_file()); 
        }
		// Save XML File
		if(XMLsave($xml, self::$properties['paths']['preferfile']) && self::$setup)
        {
            ImMsgReporter::setClause(
                    'file_succesfull_created', 
                    array('end_path' => self::$properties['paths']['preferfile'])
            );
        }
        
        // refresh process preferencess
        self::$preferences = getXML(self::$properties['paths']['preferfile']);
	}/*}}}*/



    private function create_folder_procedure($folder, $file, $file_contents)
    {/*{{{*/
        if(file_exists($folder.$file))
            return true;
        if(!mkdir($folder, 0755))
            return false;
        if(!$handle = fopen($folder.$file, 'w'))
            return false;
        fwrite($handle, $file_contents);
        fclose($handle);
        return true;
    }/*}}}*/



    private function is_file_dead($key, $pattern)
    {/*{{{*/
        $filenames = $this->item_files();
        foreach($filenames as $filename)
            if(basename($this->get_item_data(basename($filename,'.xml'), $key, true)) == $pattern)
                return false;
        return true;
    }/*}}}*/


}

if(!function_exists('return_page_slug')) {
   function return_page_slug() { 
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

function reparse_url($parsed_url) 
{ 
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : ''; 
    $host     = isset($parsed_url['host'])   ? $parsed_url['host'] : '';  
    $path     = isset($parsed_url['path'])   ? $parsed_url['path'] : ''; 
    $query    = isset($parsed_url['query'])  ? '?'.$parsed_url['query'] : '';
    $pairs = explode('&', $query);
    foreach($pairs as $pair) 
    {
        $part = explode('=', $pair);
        if($part[0] == 'page')
        {
            return ($scheme.$host.$path.'?id=imanager&cat='.ImCategory::$current_category.'&page=');
        }
    }
  return ; 
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

if(!function_exists('to7bits')) {
    function to7bits($text,$from_enc='UTF-8') {
	    if (function_exists('mb_convert_encoding')) {
		    $text = mb_convert_encoding($text,'HTML-ENTITIES',$from_enc);
	    }
	    $text = preg_replace(
	    array('/&szlig;/','/&(..)lig;/','/&([aouAOU])uml;/','/&(.)[^;]*;/'),array('ss',"$1","$1".'e',"$1"),$text);
	    return $text;
    }
}

//Function To Clean Posted Content
function safe_slash_html_input($text) {
if (get_magic_quotes_gpc()==0) 
{		
    $text = addslashes(htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false));
} else 
{		
	$text = htmlentities($text, ENT_QUOTES, 'UTF-8', false);	
}
    return $text;
}

function find_array_key($array, $key) {
    foreach($array as $index => $val) {
        if($val['slug'] == $key) return $index;
    }
    return false;
}
?>
