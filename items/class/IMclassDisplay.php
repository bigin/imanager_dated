<?php
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/
class DisplayItems
{
	//public $tags;
	public static $sortBy;
    public static $custom_tpl;

    // all available items
    private $item_pages;
    // builded pages
    private $items_builded;
    // filtered pages
    private $items_filtered;

    // pages string to show on the front-end
    public $outputPage;
    // pages array to show inside back-end
    public $adminPages;
    // pagination output
    public $pagination;
    // items number
    public $allItemsCount;
    
	
	public function __construct()
	{
		require_once(GSPLUGINPATH.'items/inc/common.php');
        self::$custom_tpl = null;

        $this->item_pages = array();
        $this->items_builded = array();
        $this->items_filtered = array();
        $this->outputPage = '';
        $this->adminPages = array();

        $this->pagination = '';
        $this->allItemsCount = 0;
	}
	
	//filter items
	private function filterAllItems($node=null)
	{
        if(!file_exists(ITEMDATAFILE))
                return false;
        $preferences = getXML(ITEMDATAFILE);

        if(is_null($node))
            if(!defined('IN_GS')  || strpos($_SERVER ['REQUEST_URI'],'/admin/') === false)
                $node = !empty($preferences->item->sortby) ? (string)$preferences->item->sortby : 'title';
            else
                $node = !empty($preferences->item->bsortby) ? (string)$preferences->item->bsortby : 'title';

        
		$pages = array();
		$i = 0;
        foreach($this->items_builded as $element)
        {
            // only front-end
            if($element['visible'] == false 
               && (!defined('IN_GS')  || strpos($_SERVER ['REQUEST_URI'],'/admin/') === false))
                continue;

            if($preferences->item->filterbyid == 1 
               && (!defined('IN_GS')  || strpos($_SERVER ['REQUEST_URI'],'/admin/') === false))
                if($_GET['id'] != $element['category'])
                    continue;
            
            // category filter
            if($element['category'] != $_REQUEST['cat'])
                continue;

            $pages[$i] = $element;
            if(!is_null($node) && !empty($element[$node]))
			    if(is_numeric($element[$node]))
				    $pages[$i][$node] = (int)$element[$node];
				else
				    $pages[$i][$node] = (string)$element[$node];
                $i++;
		}

        asort($pages);
		if(is_null($node))
            return $pages;
		
		self::$sortBy = $node;
		usort($pages, array($this, 'sortArray'));
		return $pages;
	}


	/** 
	* 
	* @param $a $b array the data to be sorted (from usort)
	* @return bool
	*/  
	private function sortArray($a, $b)
	{
       	$sortBy = self::$sortBy; //access meta data
       	$a = $a[$sortBy];
       	$b = $b[$sortBy];
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
	}


    private function pageWrap($row)
    {
        global $SITEURL;

        $this->tmp_output .= $this->outputPage;
        // store HTML template for item display
        if(!is_file(GSPLUGINPATH.'items/templates/page.list.tpl'))
            $page_tpl = '';//The "'.GSPLUGINPATH.'items/templates/page.list.tpl" Template does not exist.';
        else
            $page_tpl = file_get_contents(GSPLUGINPATH.'items/templates/page.list.tpl');
        if(!is_null(self::$custom_tpl))
            $page_tpl = !empty(self::$custom_tpl['page_tpl']) ? self::$custom_tpl['page_tpl'] : $page_tpl;

        $customfield = im_customfield_def();
        
        // generate links to detailed item page
        $pageurl = '';
        if(strpos(curPageURL(),'&page=')!==false)
                $pageurl = substr(curPageURL(),0,(strpos(curPageURL(),'&page=')-6));
		$itemurl = $pageurl.$this->items_filtered[$row]['page'].'/?item='.$this->items_filtered[$row]['slug'];

		foreach($customfield as $field)
        {
            //$data = getXML(ITEMDATA.$page['name'], 'SimpleXMLElement', LIBXML_NOCDATA);
            if(isset($this->items_filtered[$row][$field['key']]))
            {
                /*// only pics 
                if($field['type'] == 'uploader')
                    $page_tpl = preg_replace(
                        '%\[\[( *)'.$field['key'].'( *)\]\]%', 
                        $SITEURL.ITEMUPLOADDIR.$this->items_filtered[$row][$field['key']], 
                        $page_tpl
                    );
                else*/
			        $page_tpl = preg_replace(
                        '%\[\[( *)'.$field['key'].'( *)\]\]%', 
                        $this->items_filtered[$row][$field['key']], 
                        $page_tpl
                    );
            }
        }
        $page_tpl = preg_replace(
            '%\[\[( *)itemurl( *)\]\]%', 
            $itemurl, 
            $page_tpl
        );
        $page_tpl = preg_replace(
            '%\[\[( *)title( *)\]\]%', 
            $this->items_filtered[$row]['title'], 
            $page_tpl
        );
        $page_tpl = preg_replace(
            '%\[\[( *)name( *)\]\]%', 
            $this->items_filtered[$row]['name'], 
            $page_tpl
        );
        $page_tpl = preg_replace(
            '%\[\[( *)category( *)\]\]%', 
            $this->items_filtered[$row]['category'], 
            $page_tpl
        );
        // delete empty placeholders
        $page_tpl = preg_replace('%\[\[(.*)\]\]%', '', $page_tpl);

        $this->outputPage .= $page_tpl;
        
    }

    /*
    *   Generates item output inside list page template 
    */
    public function listPageBuilder($members=array(), $node=null)
    {
        global $SITEURL;
        // get item array
        $this->item_pages = glob(ITEMDATA.'*.xml');
        $this->allItemsCount = count($this->item_pages);
        $customfields = im_customfield_def($_REQUEST['cat']);

        if(count($customfields) > 1)
        {
            foreach($customfields as $field)
                if($field['type'] == 'uploader')
                    $img_fields[] = $field['key'];
                else
                    $img_fields = array();
        } else {
            $img_fields = array();
        }
        $i = 0;
        foreach($this->item_pages as $page)
        {
            $data = getXML($page);
            //echo($data->image1);
			$visible = isset($data->visible) ? $data->visible : true;
			$promo = isset($data->promo) ? isset($data->promo) : true;
            /* explicit specification of the type needed because otherwise 
               an alert appears by type definition in function 'filterAllItems()' */
			$this->items_builded[$i] = array(
                                             'slug'     => (string)$data->slug,
                                             'title'    => (string)$data->title,
                                             'page'     => (string)$data->page,
                                             'date'     => (string)$data->date,
                                             'name'     => basename($page), 
							                 'category' => (string)$data->category, 
							                 'visible'  => (string)$visible, 
							                 'promo'    => (string)$promo
                                            );
            // Array push dynamic values
            if(!empty($members) && is_array($members)) 
            { 
                foreach($members as $member)
                {
                    if(in_array($member, $img_fields))
                    {   
                        // urls
                        $this->items_builded[$i][$member] = $SITEURL.ITEMUPLOADDIR.basename((string)$data->{$member});
                    } else
                    {
                        // std properties
                        $this->items_builded[$i][$member] = stripcslashes((string)$data->{$member});
                    }    
                }
                $i++;
            }
        }
        // filter/sort all item results
        $this->items_filtered = $this->filterAllItems($node);
    }


    /*
    * Generates item components inside details page template 
    */
    public function detailsPageBuilder($members=array())
    {
        global $SITEURL;

        if(empty($members))
            return false;

		if(!isset($_GET['item'])) 
		    return false;
        
		$file =  ITEMDATA.basename($_GET['item']).'.xml';

		if(!file_exists($file))
		    return false;

		$databuff = getXML($file);
        
        // load wrapper tpl
        if(!is_file(GSPLUGINPATH.'items/templates/page.details.tpl'))
            $page_tpl = '';
        else
            $page_tpl = file_get_contents(GSPLUGINPATH.'items/templates/page.details.tpl');
        if(!is_null(self::$custom_tpl))
            $page_tpl = !empty(self::$custom_tpl['page_tpl']) ? self::$custom_tpl['page_tpl'] : $page_tpl;
        // load loop tpl
        if(!is_file(GSPLUGINPATH.'items/templates/page.details.loop.tpl'))
            $page_loop_tpl = '';
        else
            $page_loop_tpl = file_get_contents(GSPLUGINPATH.'items/templates/page.details.loop.tpl');
        if(!is_null(self::$custom_tpl))
            $page_loop_tpl = !empty(self::$custom_tpl['page_loop_tpl']) ? self::$custom_tpl['page_loop_tpl'] : $page_tpl;
        // tpl buffer
        $tpl_buff = '';

        $customfields = im_customfield_def();

        // get separate 'image' and 'loop image' fields 
        $img_fields = array();
        $img_loop_fields = array();
        // separator
        foreach($customfields as $field)
        {
            if($field['type'] == 'uploader' && strpos($field['key'],'loop-') === false)
                $img_fields[] = $field['key'];
            if($field['type'] == 'uploader' && strpos($field['key'],'loop-') !== false)
            {   // handle loop tpl
                foreach($members as $member)
                {
                    if($member == $field['key'] && !empty($databuff->{$member})) 
                    {
                        // grab the real field number 
                        $partscount = substr_count($field['key'], '-');
                        $parts = explode('-', $field['key']);
                        $img_loop_fields[] = $field['key'];
                        
                        // pushup template buffer, replace the tpl counter  
                        $tpl_buff .= $page_loop_tpl;
                        $tpl_buff = preg_replace('%\[\[( *)count( *)\]\]%', $parts[$partscount], $tpl_buff);
                    }
                }
            }
        }

        // overwrite loop tpl
        $page_loop_tpl = $tpl_buff;

        // replace loop template placeholder
        $page_tpl = preg_replace('%\[\[( *)loop-tpl( *)\]\]%', $page_loop_tpl, $page_tpl);
        // handle static tpl
        foreach($members as $member)
        { 
            // replace image urls. Only pics
            if(in_array($member, $img_fields) || in_array($member, $img_loop_fields))
            {       
                $page_tpl = preg_replace(
                    '%\[\[( *)'.$member.'( *)\]\]%', 
                    $SITEURL.ITEMUPLOADDIR.basename((string)$databuff->{$member}), 
                    $page_tpl
                );

                $pageurl = curPageURL();
                if(strpos($pageurl,'&pic=')!==false)
                    $pageurl = substr($pageurl,0,(strpos($pageurl,'&pic=')));
                $picurl = $pageurl.'&pic='.basename((string)$databuff->{$member});
                
                $page_tpl = preg_replace(
                    '%\[\[( *)url-'.$member.'( *)\]\]%', 
                    $picurl, 
                    $page_tpl
                );
            // wrapper elements
            } else
            {    
                $page_tpl = preg_replace(
                    '%\[\[( *)'.$member.'( *)\]\]%', 
                    stripcslashes((string)$databuff->{$member}), 
                    $page_tpl
                );
            }
            
        }
        
        // delete empty placeholders
        $page_tpl = preg_replace('%\[\[(.*)\]\]%', '', $page_tpl);

        $this->outputPage = $page_tpl; 
        return true;
	}


    public function pagenator()
    {
        // get settings params to define items number per page
        if(!file_exists(ITEMDATAFILE))
            return false;
        $preferences = getXML(ITEMDATAFILE);

        // Check filtered before start
        if(empty($this->items_filtered))
            return false;
        
        // check if user inside admin panel
        $admin = (!defined('IN_GS')  || strpos($_SERVER ['REQUEST_URI'],'/admin/') === false) ? false : true;

        global $SITEURL;
        // define pagination output

        // store HTML templates
        $wrapper_tpl = file_get_contents(GSPLUGINPATH.'items/templates/wrapper.pagenator.tpl');
        $link_l_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.prev.pagenator.tpl');
        $link_r_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.next.pagenator.tpl');
        $link_c_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.center.pagenator.tpl');
        $link_l_i_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.prev.inactive.pagenator.tpl');
        $link_r_i_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.next.inactive.pagenator.tpl');
        $link_c_i_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.center.inactive.pagenator.tpl');
        $link_elps_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.ellipsis.pagenator.tpl');
        $link_seclast_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.secondlast.pagenator.tpl');
        $link_last_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.last.pagenator.tpl');
        $link_first_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.first.pagenator.tpl');        
        $link_second_tpl = file_get_contents(GSPLUGINPATH.'items/templates/link.second.pagenator.tpl');
        
        // items number on page
        $limit = isset($preferences->item->itemsperpage) ? intval($preferences->item->itemsperpage) : 10;
        if($admin)
            $limit = isset($preferences->item->bitemsperpage) ? intval($preferences->item->bitemsperpage) : 10;
        // How many adjacent pages should be shown on each side
	    $adjacents = 3;

        $lastpage = ceil(count($this->items_filtered) / $limit);
        // handle get
        if(isset($_GET['page']) && $_GET['page'] <= 0)
            $_GET['page'] = 1;
        elseif(isset($_GET['page']) && $_GET['page'] > $lastpage)
            $_GET['page'] = $lastpage;
	    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
        // first page to display
        $start = !empty($page) ? (($page - 1) * $limit) : 0;
        // next page
        $next = $page + 1;

        // just for counting of rows
        $act_row = $start;
        $index = $start + $limit;

        // Wrapping HTML around an item
        while(isset($this->items_filtered[$act_row]) && $act_row < $index)
        {
            if(!$admin)
                $this->pageWrap($act_row);
            else
                $this->adminPages[] = $this->items_filtered[$act_row]['name']; 
          
            $act_row++;
        }
        
        
        // Setup page vars for display.
	    $prev = $page - 1;
	    $next = $page + 1;
        $lpm1 = $lastpage - 1;

        // fixing url inside admin: remove redundant 'page' param 
        $pageurl = $SITEURL.return_page_slug().'/?page=';
        if($admin)
            if(strpos(curPageURL(),'&page=')!==false)
                $pageurl = substr(curPageURL(),0,strpos(curPageURL(),'&page=')).'&page='; 
            else
                $pageurl = curPageURL().'&page=';

        /* Ok, now prepair Pagenator output */
        if($lastpage > 1)
        {	
            //previous button
            if($page > 1)
               $this->pagination .= preg_replace(
                   '%\[\[( *)link-href( *)\]\]%', 
                   $pageurl.$prev, 
                   $link_l_tpl
                ); 
            else
                $this->pagination .= $link_l_i_tpl;	
            
            //pages	
            if($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
            {	
                for($counter = 1; $counter <= $lastpage; $counter++)
                {
                    if($counter == $page)
                    {
                        $this->pagination .= preg_replace(
                            '%\[\[( *)counter( *)\]\]%', 
                            $counter, 
                            $link_c_i_tpl
                        );
                    } else 
                    {
                        $tmp_link_c_tpl = $link_c_tpl;
                        $tmp_link_c_tpl = preg_replace(
                            '%\[\[( *)link-href( *)\]\]%', 
                            $pageurl.$counter, 
                            $link_c_tpl
                        );
                        $this->pagination .= preg_replace(
                            '%\[\[( *)counter( *)\]\]%', 
                            $counter, 
                            $tmp_link_c_tpl
                        );
                    }
                }
            //enough pages to hide some
            } elseif($lastpage > 5 + ($adjacents * 2))
            {
                //vclose to beginning; only hide later pages
                if($page < 1 + ($adjacents * 2))		
                {
                    for($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                    {
                        if($counter == $page)
                        {
                            $this->pagination .= preg_replace(
                                '%\[\[( *)counter( *)\]\]%', 
                                $counter, 
                                $link_c_i_tpl
                            );
                        } else
                        {
                            $tmp_link_c_tpl = $link_c_tpl;
                            $tmp_link_c_tpl = preg_replace(
                                '%\[\[( *)link-href( *)\]\]%', 
                                $pageurl.$counter, 
                                $link_c_tpl
                            );

                            $this->pagination .= preg_replace(
                                '%\[\[( *)counter( *)\]\]%', 
                                $counter, 
                                $tmp_link_c_tpl
                            );
                        }
                    }
                    // ...
                    $this->pagination.= $link_elps_tpl;
                    // sec last
                    $link_seclast_tpl = preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.$lpm1, 
                        $link_seclast_tpl
                    );
                    $this->pagination .= preg_replace(
                        '%\[\[( *)counter( *)\]\]%', 
                        $lpm1, 
                        $link_seclast_tpl
                    );
                    // last
                    $link_last_tpl = preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.$lastpage, 
                        $link_last_tpl
                    );
                    $this->pagination .= preg_replace(
                         '%\[\[( *)counter( *)\]\]%', 
                         $lastpage, 
                         $link_last_tpl
                    );
                }
                // in themiddle; hide some front and some back
                elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
                {
                    // first
                    $this->pagination .= preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.'1', 
                        $link_first_tpl
                    );
                    // second
                    $this->pagination .= preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.'2', 
                        $link_second_tpl
                    );
                    // ...
                    $this->pagination.= $link_elps_tpl;
                    for($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                    {
                        if($counter == $page)
                        {
                            $this->pagination .= preg_replace(
                                '%\[\[( *)counter( *)\]\]%', 
                                $counter, 
                                $link_c_i_tpl
                            );
                        } else
                        {
                            $tmp_link_c_tpl = $link_c_tpl;
                            
                            $tmp_link_c_tpl = preg_replace(
                                '%\[\[( *)link-href( *)\]\]%', 
                                $pageurl.$counter, 
                                $link_c_tpl
                            );

                            $this->pagination .= preg_replace(
                                '%\[\[( *)counter( *)\]\]%', 
                                $counter, 
                                $tmp_link_c_tpl
                            );
                        }
                    }
                    // ...
                    $this->pagination.= $link_elps_tpl;
                    // sec last
                    $link_seclast_tpl = preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.$lpm1, 
                        $link_seclast_tpl
                    );
                    $this->pagination .= preg_replace(
                        '%\[\[( *)counter( *)\]\]%', 
                        $lpm1, 
                        $link_seclast_tpl
                    );
                    // last
                    $link_last_tpl = preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.$lastpage, 
                        $link_last_tpl
                    );
                    $this->pagination .= preg_replace(
                        '%\[\[( *)counter( *)\]\]%', 
                        $lastpage, 
                        $link_last_tpl
                    );
                }
                //close to end; only hide early pages
                else
                {
                    // first
                    $this->pagination .= preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.'1', 
                        $link_first_tpl
                    );
                    // second
                    $this->pagination .= preg_replace(
                        '%\[\[( *)link-href( *)\]\]%', 
                        $pageurl.'2', 
                        $link_second_tpl
                    );
                    // ...
                    $this->pagination.= $link_elps_tpl;
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
                    {
                        if ($counter == $page)
                        {
                            $this->pagination .= preg_replace(
                                '%\[\[( *)counter( *)\]\]%', 
                                $counter, 
                                $link_c_i_tpl
                            );
                        } else
                        {
                            $tmp_link_c_tpl = $link_c_tpl;
                            $tmp_link_c_tpl = preg_replace(
                                '%\[\[( *)link-href( *)\]\]%', 
                                $pageurl.$counter, 
                                $link_c_tpl
                            );
                            $this->pagination .= preg_replace(
                                '%\[\[( *)counter( *)\]\]%', 
                                $counter, 
                                $tmp_link_c_tpl
                            );
                        }
                    }
                }
            }
            
            //next button
            if ($page < $counter - 1) 
                $this->pagination .= preg_replace(
                    '%\[\[( *)link-href( *)\]\]%', 
                    $pageurl.$next, 
                    $link_r_tpl
                );
            else
                $this->pagination .= $link_r_i_tpl;
        }

        $this->pagination = preg_replace('%\[\[( *)value( *)\]\]%', $this->pagination, $wrapper_tpl);
        // delete empty placeholders
        $this->pagination = preg_replace('%\[\[(.*)\]\]%', '', $this->pagination);

        return true;
    }
}
?>
