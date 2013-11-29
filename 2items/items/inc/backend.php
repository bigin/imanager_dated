<?php
/**
* Plugin Name: Item Manager Extended
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/
if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

function showItemsAdmin($IM, $Reporter)
{
    $items = $IM->getItemsAdmin();
    if(empty($items))
        echo $Reporter->getClause('items/no_items');

	 // store HTML templates 
    $Reporter->langRestubsTpl('allitems.menu.wrapper.init.edit.tpl');
    $wrapper_tpl = $Reporter->tpls['allitems.menu.wrapper.init.edit.tpl'];
    $element_tpl = file_get_contents(GSPLUGINPATH.'items/templates/allitems.menu.element.init.edit.tpl');
    $tmp_element_tpl = '';

    // define jquery id
    $jid = '';
    if(isset($_GET['delete']))
        $jid = $_GET['delete'];
    elseif(isset($_GET['promo']))
        $jid = $_GET['promo'];
    elseif(isset($_GET['visible']))
        $jid = $_GET['visible'];

    // generate our item table and pagination
    $preferences = getXML(ITEMDATAFILE);
    $sort_by_field = !empty($preferences->item->bsortby) ? (string)$preferences->item->bsortby : '';
    $register = array($sort_by_field);
    $IMD = new DisplayItems();
    $IMD->listPageBuilder($register);
    $IMD->pagenator();

    $items = $IMD->adminPages;

    $pagination = $IMD->pagination;
    $i = 0;
    foreach($items as $item)
    {
        $id = basename($item, '.xml');
		$file = ITEMDATA . $item;
		$data = @getXML($file);
		$date = $data->date;
		$title = html_entity_decode($data->title, ENT_QUOTES, 'UTF-8');

        //copy tpl
        $tmp_element_tpl .= $element_tpl;

        // colorize the rows
        if($i > 0)
        {
            $tmp_element_tpl = preg_replace(
                '%\[\[( *)count( *)\]\]%', 
                'im-arow', 
                $tmp_element_tpl
            );
            $i = 0;
        } else {
            $tmp_element_tpl = preg_replace(
                '%\[\[( *)count( *)\]\]%', 
                'im-brow', 
                $tmp_element_tpl
            );
            $i = 1;
        }
        // id
        $tmp_element_tpl = preg_replace(
            '%\[\[( *)item-id( *)\]\]%', 
            $id, 
            $tmp_element_tpl
        );
        //title
        $tmp_element_tpl = preg_replace(
            '%\[\[( *)itemmanager-title( *)\]\]%', 
            IMTITLE, 
            $tmp_element_tpl
        );
        //item title
        $tmp_element_tpl = preg_replace(
            '%\[\[( *)item-title( *)\]\]%', 
            $title, 
            $tmp_element_tpl
        );
        //item category
        $tmp_element_tpl = preg_replace(
            '%\[\[( *)item-category( *)\]\]%', 
            $data->category, 
            $tmp_element_tpl
        );
        //item date
        $tmp_element_tpl = preg_replace(
            '%\[\[( *)item-date( *)\]\]%', 
            $date, 
            $tmp_element_tpl
        );
        // Prepair visible icon template & link
        $v_icon_tpl = file_get_contents(GSPLUGINPATH.'items/templates/allitems.vicon.init.edit.tpl');

        $v_icon_class = 'redoff';
        if(!isset($data->visible) || $data->visible == true)
		    $v_icon_class = 'redon';
        
        $v_icon_tpl = preg_replace(
            '%\[\[( *)visible-class( *)\]\]%', 
            $v_icon_class, 
            $v_icon_tpl
        );
        //visible icon
        $tmp_element_tpl = preg_replace(
            '%\[\[( *)visible-icon( *)\]\]%', 
            $v_icon_tpl, 
            $tmp_element_tpl
        );
        // Prepair promo icon template & link
        $p_icon_tpl = file_get_contents(GSPLUGINPATH.'items/templates/allitems.picon.init.edit.tpl');

        $p_icon_class = 'redoff';
        if(!isset($data->promo) || $data->promo == true)
		    $p_icon_class = 'redon';
        
        $p_icon_tpl = preg_replace(
            '%\[\[( *)promo-class( *)\]\]%', 
            $p_icon_class, 
            $p_icon_tpl
        );
        //promo icon
        $tmp_element_tpl = preg_replace(
            '%\[\[( *)promo-icon( *)\]\]%', 
            $p_icon_tpl, 
            $tmp_element_tpl
        );

    }

    //prepare wrapper tpl now
    $wrapper_tpl = preg_replace(
        '%\[\[( *)item-id( *)\]\]%', 
        $jid, 
        $wrapper_tpl
    );
    $wrapper_tpl = preg_replace(
        '%\[\[( *)itemmanager-title( *)\]\]%', 
        IMTITLE, 
        $wrapper_tpl
    );
    $wrapper_tpl = preg_replace(
        '%\[\[( *)content( *)\]\]%', 
        $tmp_element_tpl, 
        $wrapper_tpl
    );
    $wrapper_tpl = preg_replace(
        '%\[\[( *)count( *)\]\]%', 
        $IMD->allItemsCount, 
        $wrapper_tpl
    );
    // pagination
    $wrapper_tpl = preg_replace(
        '%\[\[( *)pagination( *)\]\]%', 
        $pagination, 
        $wrapper_tpl
    );

    // delete empty placeholders
    $wrapper_tpl = preg_replace('%\[\[(.*)\]\]%', '', $wrapper_tpl);

    echo $wrapper_tpl;
}

function admin_header($Reporter)
{
    // rules just for safety reasons 
    $labels = array('settings', 'fields', 'category', 'edit', 'view');
    // store HTML templates
    $Reporter->langRestubsTpl('header.init.edit.tpl');
    $content_tpl = $Reporter->tpls['header.init.edit.tpl'];
    
    $content_tpl = preg_replace(
        '%\[\[( *)itemmanager-title( *)\]\]%', 
        IMTITLE, 
        $content_tpl
    );
    // colourise links
    $f = false;
    foreach($labels as $label)
        if(isset($_GET[$label]))
        {
            $content_tpl = preg_replace(
                '%\[\[( *)'.$label.'( *)\]\]%', 
                'class = "current"', 
                $content_tpl
            );
            $f = true;
        }
    // just for colorate "viev all" menu point
    if(!$f)
        $content_tpl = preg_replace('%\[\[( *)view( *)\]\]%', 'class = "current"',$content_tpl);

    $content_tpl = preg_replace('%\[\[(.*)\]\]%', '', $content_tpl);
    echo $content_tpl;
}
	
function showEditItem($IM, $id, $Reporter)
{
    $file = ITEMDATA . $id . '.xml';
    $data = @getXML($file);
    $title =    @stripslashes($data->title);
    $itempage = @stripslashes($data->page);
    $category = @stripslashes($data->category);
    $content  = @stripslashes($data->content);
    $excerpt  = @stripslashes($data->excerpt);

    // store HTML templates
    $content_tpl = file_get_contents(GSPLUGINPATH.'items/templates/edititem.menu.init.edit.tpl');
    $content_option_tpl = file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');

    
    // prepair selectbox category
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'Choose category...', 
        $content_option_tpl
    );
    $category_file = getXML(ITEMDATAFILE);
    foreach($category_file->categories->category as $val)
    {
        $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');

        $selected = '';
        if($category == $val)
            $selected = 'selected';
        $content_option_tpl = preg_replace(
            '%\[\[( *)option-value( *)\]\]%', 
            $val, 
            $content_option_tpl
        );
        $content_option_tpl = preg_replace(
            '%\[\[( *)selected( *)\]\]%', 
            $selected, 
            $content_option_tpl
        );
    }

    $replacement = '[[items/edit_item]]';
    if(empty($data)) 
        $replacement = '[[items/create_item]]';

    $content_tpl = preg_replace(
        '%\[\[( *)item-menu-titel( *)\]\]%', 
        $replacement, 
        $content_tpl
    );

    $Reporter->langRestubsTpl( 'edititem.menu.init.edit.tpl', $content_tpl );
    $content_tpl = $Reporter->tpls['edititem.menu.init.edit.tpl'];

    $content_tpl = preg_replace(
        '%\[\[( *)itemmanager-title( *)\]\]%', 
        IMTITLE, 
        $content_tpl
    );
    $content_tpl = preg_replace(
        '%\[\[( *)item-id( *)\]\]%', 
        $id, 
        $content_tpl
    );
    $content_tpl = preg_replace(
        '%\[\[( *)option-tpl( *)\]\]%', 
        $content_option_tpl, 
        $content_tpl
    );
    $content_option_tpl = file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');

    
    // prepare selectbox page
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'Choose page...', 
        $content_option_tpl
    );
    $category_file = getXML(ITEMDATAFILE);

    // getSimple function to get all pages
    $pages = get_available_pages();
    foreach($pages as $page)
    {
        $slug = $page['slug'];

        $selected = '';
        if($slug == $itempage)
            $selected = 'selected';
            
        $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');

        $content_option_tpl = preg_replace(
            '%\[\[( *)option-value( *)\]\]%', 
            $slug,
            $content_option_tpl
        );
        $content_option_tpl = preg_replace(
            '%\[\[( *)selected( *)\]\]%', 
            $selected, 
            $content_option_tpl
        );
    }
    $content_tpl = preg_replace(
        '%\[\[( *)option-tpl-page( *)\]\]%', 
        $content_option_tpl, 
        $content_tpl
    );
    $content_option_tpl = file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');


    // title field
    $content_tpl = preg_replace(
        '%\[\[( *)item-title( *)\]\]%', 
        $title, 
        $content_tpl
    );

    $content_tpl = preg_replace(
        '%\[\[( *)item-title( *)\]\]%', 
        $title, 
        $content_tpl
    );
    
    $cfa = returnCustomFields();

    $content_tpl = preg_replace(
        '%\[\[( *)custom-fields( *)\]\]%', 
        $cfa, 
        $content_tpl
    );

    // delete empty placeholders
    $content_tpl = preg_replace('%\[\[(.*)\]\]%', '', $content_tpl);

    echo $content_tpl;
}
	
function showEditCategories($IM, $Reporter)
{
    global $PRETTYURLS;
    if(!file_exists(ITEMDATAFILE))
        return false;
    $category_file = getXML(ITEMDATAFILE);

    // store HTML templates
    $Reporter->langRestubsTpl('category.wrapper.menu.init.edit.tpl');
    $content_tpl = $Reporter->tpls['category.wrapper.menu.init.edit.tpl'];
    $row_tpl = file_get_contents(GSPLUGINPATH.'items/templates/category.menu.init.edit.tpl');

    $tmp_row_tpl = '';

    $i = 0;
    //rows 
    foreach($category_file->categories->category as $category)
    {
        $tmp_row_tpl .= preg_replace(
            '%\[\[( *)category( *)\]\]%', 
            $category, 
            $row_tpl
        );
        if($i > 0)
        {
            $tmp_row_tpl = preg_replace(
                '%\[\[( *)cont( *)\]\]%', 
                'im-arow', 
                $tmp_row_tpl
            );
            $i = 0;
        } else
        {
            $tmp_row_tpl = preg_replace(
                '%\[\[( *)cont( *)\]\]%', 
                'im-brow', 
                $tmp_row_tpl
            );
            $i = 1;
        }
    }
    
    //wrapper
    $content_tpl = preg_replace(
        '%\[\[( *)value( *)\]\]%', 
        $tmp_row_tpl, 
        $content_tpl
    );

     // delete empty placeholders
    $content_tpl = preg_replace('%\[\[(.*)\]\]%', '', $content_tpl);

    echo $content_tpl;
}

function showImSettings($Reporter)
{
    if(file_exists(ITEMDATAFILE))
    {
        $preferences = getXML(ITEMDATAFILE);
        $file_title = $preferences->item->title;
        $file_item_number = $preferences->item->itemsperpage;
        $file_checked = $preferences->item->filterbyid;
        $sort_by = $preferences->item->sortby;

        // back-end
        $file_bitem_number = $preferences->item->bitemsperpage;
        $bsort_by = $preferences->item->bsortby;
        $file_bchecked = $preferences->item->bfilterbyid;
        $file_thumbnail_w = !empty($preferences->item->thumbwidth) ? $preferences->item->thumbwidth : 200;
    }

    // store HTML template
    $Reporter->langRestubsTpl('settings.menu.init.edit.tpl');
    $content_tpl = $Reporter->tpls['settings.menu.init.edit.tpl'];
    // use "edititem" template cause that's the same as we need now. 
    $content_option_tpl = file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');
    
    // prepair selectbox category
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'Choose a node...', 
        $content_option_tpl
    );
    $category_file = getXML(ITEMDATAFILE);

    $cf_params_array = im_customfield_def(); 

    if(!is_array($cf_params_array))
        $cf_params_array = array();
    foreach($cf_params_array as $item)
    {
        $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');

        $content_option_tpl = preg_replace(
            '%\[\[( *)option-value( *)\]\]%', 
            $item['key'], 
            $content_option_tpl
        );
        $selected = '';
        if($sort_by == $item['key'])
            $selected = 'selected';

        $content_option_tpl = preg_replace(
            '%\[\[( *)selected( *)\]\]%', 
            $selected, 
            $content_option_tpl
        );
    }

    $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'title', 
        $content_option_tpl
    );
    $selected = '';
    if($sort_by == 'title')
        $selected = 'selected';

    $content_option_tpl = preg_replace(
        '%\[\[( *)selected( *)\]\]%', 
        $selected, 
        $content_option_tpl
    );

    $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'category', 
        $content_option_tpl
    );
    $selected = '';
    if($sort_by == 'category')
        $selected = 'selected';

    $content_option_tpl = preg_replace(
        '%\[\[( *)selected( *)\]\]%', 
        $selected, 
        $content_option_tpl
    );
    $content_tpl = preg_replace(
        '%\[\[( *)option-tpl-page( *)\]\]%', 
        $content_option_tpl, 
        $content_tpl
    );


    $content_tpl = preg_replace(
        '%\[\[( *)manager-title( *)\]\]%', 
        $file_title, 
        $content_tpl
    );
    if($file_checked == 1)
        $content_tpl = preg_replace(
            '%\[\[( *)category-checked( *)\]\]%', 
            'checked', 
            $content_tpl
        );
    else
        $content_tpl = preg_replace(
            '%\[\[( *)category-checked( *)\]\]%', 
            '', 
            $content_tpl
        );

    if(empty($file_item_number))
        $file_item_number = 10;

    $content_tpl = preg_replace(
        '%\[\[( *)items-per-page( *)\]\]%', 
        $file_item_number, 
        $content_tpl
    );


    /** BACK-END SETTINGS SECTION **/

    $content_option_tpl = file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');

    // prepair selectbox category
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'Choose a node...', 
        $content_option_tpl
    );

    foreach($cf_params_array as $item)
    {
        $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');

        $content_option_tpl = preg_replace(
            '%\[\[( *)option-value( *)\]\]%', 
            $item['key'], 
            $content_option_tpl
        );
        $selected = '';
        if($bsort_by == $item['key'])
            $selected = 'selected';

        $content_option_tpl = preg_replace(
            '%\[\[( *)selected( *)\]\]%', 
            $selected, 
            $content_option_tpl
        );
    }

    $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'title', 
        $content_option_tpl
    );
    $selected = '';
    if($bsort_by == 'title')
        $selected = 'selected';

    $content_option_tpl = preg_replace(
        '%\[\[( *)selected( *)\]\]%', 
        $selected, 
        $content_option_tpl
    );

    $content_option_tpl .= file_get_contents(GSPLUGINPATH.'items/templates/edititem.option.menu.init.edit.tpl');
    $content_option_tpl = preg_replace(
        '%\[\[( *)option-value( *)\]\]%', 
        'category', 
        $content_option_tpl
    );
    $selected = '';
    if($bsort_by == 'category')
        $selected = 'selected';

    $content_option_tpl = preg_replace(
        '%\[\[( *)selected( *)\]\]%', 
        $selected, 
        $content_option_tpl
    );
    $content_tpl = preg_replace(
        '%\[\[( *)b-option-tpl-page( *)\]\]%', 
        $content_option_tpl, 
        $content_tpl
    );

    if($file_bchecked == 1)
        $content_tpl = preg_replace(
            '%\[\[( *)bcategory-checked( *)\]\]%', 
            'checked',
            $content_tpl
        );
    else
        $content_tpl = preg_replace(
            '%\[\[( *)bcategory-checked( *)\]\]%', 
            '', 
            $content_tpl
        );

    if(empty($file_bitem_number))
        $file_bitem_number = 10;

    $content_tpl = preg_replace(
        '%\[\[( *)bitems-per-page( *)\]\]%', 
        $file_bitem_number, 
        $content_tpl
    );

    // thumb properties
    $content_tpl = preg_replace(
        '%\[\[( *)thumb-w( *)\]\]%', 
        $file_thumbnail_w, 
        $content_tpl
    );

    // delete empty template placeholders
    $content_tpl = preg_replace('%\[\[(.*)\]\]%', '', $content_tpl);

    echo $content_tpl;  
}

// items management function that shows our "custom fields" 
// on the "Create new item" or "Edit item" panel
function returnCustomFields()
{
    
    // include Upload Script 
    include(GSPLUGINPATH.'items/uploadscript/upload.php');

    global $SITEURL;
      
    $id = isset($_GET['edit']) ?  basename($_GET['edit']) : '';
    $file = (!empty($id)) ? ITEMDATA . $id . '.xml' : '';
    $data_edit = @getXML($file);
    // SimpleXML to read from (defined in common.php) 
    $list_fields = im_customfield_def();

    if (!$list_fields || count($list_fields) <= 0) 
        return false;

    //get script preferences
    if(!file_exists(ITEMDATAFILE))
        return false;
    $preferences = getXML(ITEMDATAFILE);

    // store HTML templates 
    $wrapper_tpl = file_get_contents(GSPLUGINPATH.'items/templates/wrapper.init.edit.tpl');
    $element_wrapper_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.wrapper.init.edit.tpl');
    $element_hidden_wrapper_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.hidden.wrapper.init.edit.tpl');
    $element_input_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.input.init.edit.tpl');
    $element_dropdown_wrapper_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.dropdown.wrapper.init.edit.tpl');
    $element_dropdown_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.dropdown.init.edit.tpl');
    $element_checkbox_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.checkbox.init.edit.tpl');
    $element_area_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.area.init.edit.tpl');
    $element_hidden_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.hidden.init.edit.tpl');
    $element_thumb_tpl = file_get_contents(GSPLUGINPATH.'items/templates/element.thumb.init.edit.tpl');

    // define element bundles
    $elements = '';
    $sections = '';
    // define options array
    $options = array();
    // templates rendering section
    foreach ($list_fields AS $element) 
    {
        // copy original templates for further tasks 
        $wrapper     = $element_wrapper_tpl;
        $hiddenwrapp = $element_hidden_wrapper_tpl;
        $input       = $element_input_tpl;
        $dropwrapper = $element_dropdown_wrapper_tpl;
        $checkbox    = $element_checkbox_tpl;
        $area        = $element_area_tpl;
        $hidden      = $element_hidden_tpl;
        $uploader    = '';
        $thumb       = $element_thumb_tpl;

        // get element attributes
        $key   = strtolower($element['key']);
        $label = $element['label'];
        $type  = $element['type'];
        if(isset($element['options']) && is_array($element['options']))
            $options = $element['options'];
        // get field value 
        $select = !empty($element['value']) ? $element['value'] : '';
        // edit item only
        if (isset($_GET['edit']) && !empty($_GET['edit']))
            $select = $data_edit->$key;
            
        // define some template search params & facing placements
        $input_search = array('element-key', 'element-type', 'input-element-class', 'element-id', 'element-value');
        $input_text_replace = array('post-'.$key, 'text', 'im-input-text', strtolower($key), $select);
        $checkbox_search = array('element-id', 'element-class', 'element-name');
        $checkbox_replace = array(strtolower($key), 'im-checkbox', 'post-'.$key);
        $area_search = array('element-id', 'element-class', 'element-name', 'value');
        $area_replace = array(strtolower($key), 'im-area', 'post-'.$key, $select);
        $hidden_search = array('element-id', 'element-class', 'element-type', 'element-key', 'value');
        $hidden_replace = array(strtolower($key), 'im-hidden', 'hidden', 'post-'.$key, $select);

        // 0 = default unknow type, 1 = input, 2 = dropbox, 3 = checkbox, 4 = textarea, 5 = hidden, 6 = file upload 
        $flag = 0;
        // replace element placeholders by field type
        switch($type)
        {
            case 'text':
                foreach($input_search AS $k => $search)
                    $input = preg_replace(
                        '%\[\[( *)'.$search.'( *)\]\]%', 
                        stripcslashes($input_text_replace[$k]), 
                        $input
                    );
                $flag = 1;
                break;
            case 'dropdown':
                foreach($options AS $option)
                {
                    $dropdown = $element_dropdown_tpl;
                    if($option == $select)
                        $dropdown = preg_replace('%\[\[( *)selected( *)\]\]%', 'selected ', $dropdown);
                    $dropdown = preg_replace('%\[\[( *)value( *)\]\]%', $option, $dropdown);
                    $dropdown = preg_replace('%\[\[( *)value-text( *)\]\]%', $option, $dropdown);
                    // delete empty placeholder
                    $dropdown = preg_replace('%\[\[(.*)\]\]%', '', $dropdown);
                    $sections .= $dropdown; 
                }
                $dropwrapper = preg_replace('%\[\[( *)element-id( *)\]\]%', strtolower($key), $dropwrapper);
                $dropwrapper = preg_replace('%\[\[( *)element-key( *)\]\]%', 'post-'.$key, $dropwrapper);
                $dropwrapper = preg_replace('%\[\[( *)dropdown-wrapper-class( *)\]\]%', 'im-dropdown', $dropwrapper);
                $dropwrapper = preg_replace('%\[\[( *)value( *)\]\]%', $sections, $dropwrapper);
                $flag = 2;
                break;
            case 'checkbox':
                foreach($checkbox_search AS $k => $search)
                    $checkbox = preg_replace(
                        '%\[\[( *)'.$search.'( *)\]\]%', 
                        $checkbox_replace[$k], 
                        $checkbox
                    );
                $checkbox = preg_replace(
                    '%\[\[( *)checked( *)\]\]%', 
                    $select ? 'checked="checked"' : '', 
                    $checkbox
                );
                $checkbox = preg_replace(
                    '%\[\[( *)element-value( *)\]\]%', 
                    !empty($select) ? $select : '', 
                    $checkbox
                );
                $flag = 3;
                break;
            case 'textfull':
                foreach($area_search AS $k => $search)
                    $area = preg_replace(
                        '%\[\[( *)'.$search.'( *)\]\]%', 
                        $area_replace[$k], 
                        $area
                    );
                $flag = 4;
                break;
            case 'hidden':
                foreach($hidden_search AS $k => $search)
                    $hidden = preg_replace(
                        '%\[\[( *)'.$search.'( *)\]\]%', 
                        $hidden_replace[$k], 
                        $hidden
                    );
                $flag = 5;
                break;
            case 'uploader':
                ob_start();
                genOutput('post-'.$key);
                $uploader = ob_get_clean();
                try {while (ob_get_level() > 0) ob_end_flush();} catch( Exception $e ) {}

                $uploader = preg_replace(
                    '%\[\[( *)element-id( *)\]\]%', 
                    strtolower($key), 
                    $uploader
                );
                // thumb
                if(isset($_GET['edit']) && !empty($_GET['edit']) &&
                   file_exists($select))
                {
                    $imginfo = @getimagesize($select);
                    
                    $w = $imginfo[0];
                    $h = $imginfo[1];
                     
                    $tw = $preferences->item->thumbwidth;
                    
                    if($tw >= $w && !empty($w)) 
                    {
                        $tw = $w;
                        $th = $h;
                    } else if(empty($w)) 
                    {
                        return false;
                    } else 
                    { 
                        $th = intval($h * $tw / $w);
                    }
                    // thumb width
                    $thumb = preg_replace(
                        '%\[\[( *)thumb-w( *)\]\]%', 
                        $tw, 
                        $thumb
                    );
                    // thumb height
                    $thumb = preg_replace(
                        '%\[\[( *)thumb-h( *)\]\]%', 
                        $th, 
                        $thumb
                    );
                    // thumb path
                    $thumb = preg_replace(
                        '%\[\[( *)thumb-path( *)\]\]%', 
                        $SITEURL.ITEMUPLOADDIR.basename($select), 
                        $thumb
                    ).'<br />';
                } else 
                    $thumb = '';
                
                $flag = 6;
                break;
        }

        // delete empty placeholders
        $input = preg_replace('%\[\[(.*)\]\]%', '', $input);
        $dropwrapper = preg_replace('%\[\[(.*)\]\]%', '', $dropwrapper);
        $checkbox = preg_replace('%\[\[(.*)\]\]%', '', $checkbox);
        $area = preg_replace('%\[\[(.*)\]\]%', '', $area);
        $hidden = preg_replace('%\[\[(.*)\]\]%', '', $hidden);
        $uploader = preg_replace('%\[\[(.*)\]\]%', '', $uploader);
        $thumb = preg_replace('%\[\[(.*)\]\]%', '', $thumb);

        if($flag > 0) 
        {
            if($flag != 5)
            {
                // replace element wrapper placeholder by label
                $wrapper = preg_replace(
                    '%\[\[( *)label( *)\]\]%', 
                    strtolower($key), 
                    $wrapper
                );
                // replace element wrapper placeholder by label-text
                $wrapper = preg_replace(
                    '%\[\[( *)label-text( *)\]\]%', 
                    stripcslashes($label), 
                    $wrapper
                );
            }
            // text field
            if($flag == 1)
                $wrapper = preg_replace(
                    '%\[\[( *)value( *)\]\]%', 
                    stripcslashes($input), 
                    $wrapper
                );
            // selectbox
            if($flag == 2)
                $wrapper = preg_replace(
                    '%\[\[( *)value( *)\]\]%', 
                    stripcslashes($dropwrapper), 
                    $wrapper
                );
            // checkbox
            if($flag == 3)
                $wrapper = preg_replace(
                    '%\[\[( *)value( *)\]\]%', 
                    $checkbox, 
                    $wrapper
                );
            // textarea
            if($flag == 4)
                $wrapper = preg_replace(
                    '%\[\[( *)value( *)\]\]%', 
                    stripcslashes($area), 
                    $wrapper
                );
            // hidden field
            if($flag == 5)
                $wrapper = preg_replace(
                    '%\[\[( *)value( *)\]\]%', 
                    $hidden, 
                    $hiddenwrapp
                );
            // file uploader
            if($flag == 6) {
                $parts = explode(';', $select);
                $part = !empty($parts) ? $parts[0] : $select;
                $wrapper = preg_replace(
                    '%\[\[( *)value( *)\]\]%', 
                    $thumb.$uploader, 
                    $wrapper
                    // Fill the input with a file path when the file is selected
                ).'<script charset="utf-8" type="text/javascript">'."\n".
                  '$(document).ready(function(){'."\n".
                  '$("#'.strtolower($key).'").val("'.$select.'");'."\n".
                  '});'."\n".'</script>'."\n";
            }
        } else
        {
            $wrapper = '';
        }

        $elements .= $wrapper;
    }
    // replace wrapper placeholder by element wrapper
    $wrapper_tpl = preg_replace(
        '%\[\[( *)value( *)\]\]%', 
        $elements, 
        $wrapper_tpl
    );
    return $wrapper_tpl;
}

function showRenameTool($Reporter)
{
    // store HTML templates
    $Reporter->langRestubsTpl('rename.tool.init.edit.tpl');
    $rename_tool_tpl = $Reporter->tpls['rename.tool.init.edit.tpl'];
    
    $rename_tool_tpl = preg_replace(
        '%\[\[( *)formaction( *)\]\]%', 
        curPageURL(), 
        $rename_tool_tpl
    );

    echo $rename_tool_tpl;
}

// this function includes custom CSS file (back-end only)
function imcss_showcss() {
	$css_file = 'im-styles.css';
	if (file_exists(GSPLUGINPATH.IMCSSPATH.$css_file) &&
        defined('IN_GS')) 
    {
		global $SITEURL;
		echo '<!-- im_css -->'."\n";
        echo '<link type="text/css" rel="stylesheet" href="'.$SITEURL.'plugins/'.IMCSSPATH.$css_file.'" />'."\n";
	}
}
?>
