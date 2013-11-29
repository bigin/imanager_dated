<?php
/** ~Controller~ 
*   forwards the handing of the requests to view and model classes.
*/
class ImController
{
    private $im;
    private $imo;
    private $input;

    public function __construct($input=array())
    {
        /* input $_GET and $_POST */
        $this->input = !empty($input) ? $input : array_merge($_GET, $_POST);
        // initialize our classes
        $this->im = new ImModel($this->input);
        $this->imo = new ImOutput(ImModel::getProp('paths', 'templatedir'));
        $this->imc = new ImHtmlConstructor($this->imo);
    }



    /*  */
    public function displayBackEnd()
    {

        $o = array('head' => '', 'msg' => '', 'selector' => '', 'content' => '' );
        // firstly, check Category-Reloader request
        if(isset($_SESSION['cat']) && !empty($_SESSION['cat']))
        {
            // set category and regrab item data
            ImCategory::setCategory($_SESSION['cat']);

        }
        // generate tab panel output
        $o['head'] = $this->tabpanel();
        // errors already send?
        $msg = ImMsgReporter::msgs();
        // output edit item menu
        if(isset($this->input['edit']))  
        {
            if(ImCategory::$is_cat_exist)
                $o['content'] = $this->itemeditor();
	    }
        elseif(isset($this->input['cat']))
        {
        	ImCategory::setCategory($this->input['cat']);
  			$_SESSION['cat'] = (string) ImCategory::$current_category;
        	return $this->itemlister(true);
        }
        // delete item
	    elseif (isset($this->input['delete']))
	    {
            if($this->im->item_delete($this->input['delete']))
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemlister();
            }
        } 
        // change visibility
	    elseif (isset($this->input['visible'])) 
	    {
		    if($this->im->visibility_change())
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemlister();
            }
	    }
        // change promotion 
	    elseif (isset($this->input['promo'])) 
	    {
		    if($this->im->promotion_change())
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemlister();
            }
	    }
        // save category & settings
	    elseif (isset($this->input['category_edit']))
	    {      
		    $this->im->setupconfig(); 
            $o['content'] = $this->categoryregister(); 
	    }
        // delete category
	    elseif (isset($this->input['deletecategory']))
	    {     
		    $this->im->setupconfig();
            $o['content'] = $this->categoryregister();  
	    }
        // category menu
	    elseif (isset($this->input['category'])) 
	    {      
            $o['content'] = $this->categoryregister();	 
	    }
	    elseif (isset($this->input['settings_edit']))
	    {
		    // todo
	    }
	    elseif (isset($this->input['settings'])) 
	    {    
            $o['content'] = $this->setupdisplay();
		    // todo tlz.bh
	    } 
        // save item
        elseif (isset($this->input['submit'])) 
	    {      
		    if(!$this->im->saveitem())
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemlister();
            } else 
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemlister();
            }
	    }
        // configure custom fields
	    elseif (isset($this->input['fields']))
	    {
            // rename fields
            if(isset($this->input['sender']))
            {
                $this->im->rename_item_fields();
            }
            if(isset($this->input['save']))
                $this->im->fieldsgenerator();
		    $o['content'] = $this->fieldsconfigurator();
            $o['content'] .= $this->renametool();
	    }
        // show item list menu
	    elseif(!ImModel::$setup && !$msg)
	    {
            $o['content'] = $this->itemlister();
	    }

        if(/*!isset($this->input['edit']) 
            &&*/ !isset($this->input['category']) 
            && !isset($this->input['settings']))
        {
            $o['selector'] = $this->catselector();
        }
        $o['msg'] = $this->msg();
        return $this->output($o);
    }



    public function runModelMethod($key, $val=''){
        if(!empty($val))  
            return $this->im->{$key}($val);
        return $this->im->{$key}($val);
    }



    public function getModelValue($key){
        return $this->im->{$key};
    }



    /** inject your templates here */
    public function tplRegister(array $entry)
    {
        foreach($entry as $label => $file)
        {
            if(file_exists($file))
            {
                $fileinf = pathinfo($file);
                if(strtolower($fileinf['extension']) == 'tpl')
                {
                    $this->im->setTplKit($label, file_get_contents($file));
                    continue;
                } else 
                {
                    ImMsgReporter::setClause('err_file_extension');
                    return false;
                }
            }
            // read as a string
            $this->im->setTplKit($label, $file);
            continue;
        }
        return true;
    }



    /** replace template platceholder and output template */
    public function paint($key, array $values = array()) 
    {
        if(empty($values))
            return $this->imo->output(ImModel::getTplKit($key));
        return $this->imo->output(ImModel::getTplKit($key), $values);
    }



    /** displays admin tab panel */
    private function tabpanel() 
    {
        $tpl = ImModel::getTplKit('tabpanel');
        // rules just for safety reasons/*{{{*/
        $tvs = array();
        $labels = array('settings', 'fields', 'category', 'edit', 'view');
        $f = false;
        if(isset($this->input['reloader']))
            $this->input['edit'] = '';
        foreach($labels as $label)
        {
            if(isset($this->input[$label]))
            {
                $tvs[$label] = 'current';
                $f = true;
                break;
            }
        }
        // colorate "viev all" menu point
        if(!$f)
            $tvs['view'] = 'current';
        $tvs['itemmanager-title'] = IMTITLE;
        // output header
        return $this->imo->output($tpl, $tvs, true, array(), true);/*}}}*/
    }
    


    /* admin edit item menu */
    private function itemeditor()
    {/*{{{*/
        $tpls = ImModel::getTplKit('itemeditor');
        $title    = '';
        $itempage = '';
        $category = '';
        $content  = '';
        $excerpt  = '';
        $msg = ImMsgReporter::msgs();
        if(!empty($msg))
            return false;
        $o = '';
        $id = empty($this->input['edit']) ? uniqid() : $this->input['edit'];
        // item edit or generate new item?
        $edit = empty($this->input['edit']) ? false : true;
        // try to get the current page for our back link
        
        $backpage = !empty($this->input['page']) ? (int) $this->input['page'] : 1; 
        $itemdata = $this->im->get_item_data($id, '', true);
        if($itemdata)
        {
            $title    =  safe_slash_html_input($itemdata->title);
            $itempage =  safe_slash_html_input($itemdata->page);
            $category =  safe_slash_html_input($itemdata->category);
            $content  =  safe_slash_html_input($itemdata->content);
            $excerpt  =  safe_slash_html_input($itemdata->excerpt);
            ImCategory::setCategory($category);
        }
        // set up category, restore item data
        $this->im->itemdata_setup_restore($id);

        $cfile = ImModel::getPref();
        $tvs = '';
        foreach($cfile->categories->category as $cat)
        {
            $tag = (ImCategory::$current_category == $cat) ? 'selected' : '';
            $tvs .= $this->imo->output($tpls[1], array('selected' => $tag, 'option-value' => $cat), true);
        }

        $replacement = '[[imanager/edit_item]] [[imanager/element]]';
        if(empty($this->im->itemdata))
            $replacement = '[[imanager/create_item]] [[imanager/element]]';

        $o = $this->imo->output($tpls[0], array('item-menu-titel' => $replacement,
            'item-id' => $id,
            'back-page' => $backpage,
            'item-edit' => $edit,
            'option-tpl' => $tvs), true);
        
        // GS function to get all pages
        $pages = get_available_pages();
        $tvs = '';
        // select page to display item details
        foreach($pages as $page)
        {
            $tag = ($page['slug'] == $itempage) ? 'selected' : '';
            $tvs .= $this->imo->output($tpls[1], array('option-value' => $page['slug'], 'selected' => $tag), true);
        }
        $o = $this->imo->output($o, array('option-tpl-page' => $tvs,
            'item-title' => $title,
            'option-tpl' => $tvs), true);

        // generates custom fields output
        $cfa_o = $this->customfields($id, $tpls);
        $o = $this->imo->output($o, array('custom-fields' => $cfa_o), true, array(),true);

        return $o;/*}}}*/
    }




    /**
    * displays item list inside admin panel 
    * 
    * @list_only = true 
    * returns list only exclude wrapper 
    */

    private function itemlister($list_only = false)
    {
        $tpls = ImModel::getTplKit('itemregister');
        $tpls[4] = ImModel::getTplKit('contentwrapper');
        $pref = ImModel::getPref();
        $sort_by_field = !empty($pref) ? 
            (string)$pref->item->bsortby : 'title';
        $reg = $sort_by_field;
        /* todo: register any other real field */
        if(!$this->im->gen_register(array(),$reg))
            return false;

        //$tmp_row_o = '';//$tpls[1];
        $i = 0;
        // call html generator to generate the item list 
        $tmp_row_o = $this->item_list_generator();
        // return list only without div wrapper
        if($list_only)
        	return $tmp_row_o; 

        return $this->imo->output($tpls[0], array(
               'item-id' => $this->im->pagedata['jid'],
               'content-wrapper' => $this->imo->output($tpls[4], array(
               		'content' => $tmp_row_o)
               ),
               'count' => count($this->im->items_ordered_struct),
               'pagination' => $this->paginator(ImModel::getTplKit('paginator'))), true, array(), true);
    }

    /* the method generates html item list */
    private function item_list_generator() 
	{
		$tpls = ImModel::getTplKit('itemregister');
		// buffered templates
		$tmp_row_o = '';
		$i = 0;
    	foreach($this->im->pagedata['itemkeys'] as $key)
        {
            $globbuf = $tpls[1];
            $tmp_vic_o = $tpls[2];
            $tmp_pic_o = $tpls[3];
            //$tmp_row_o .= $tpls[1];
            $id = $this->im->items_ordered_struct[$key]['slug'];
		    $file = ImModel::getProp('paths', 'uploaddir').'/'.$this->im->items_ordered_struct[$key]['name'];
		    $date = $this->im->items_ordered_struct[$key]['date'];
		    $title = html_entity_decode($this->im->items_ordered_struct[$key]['title'], ENT_QUOTES, 'UTF-8');
            //$cat = html_entity_decode($this->im->items_ordered_struct[$key]['category'], ENT_QUOTES, 'UTF-8');
            
            // coloring the rows
            if($i > 0)
            {
                $globbuf = $this->imo->output($globbuf, array('count' => 'im-arow'));
                $i = 0;
            } else 
            {
                $globbuf = $this->imo->output($globbuf, array('count' => 'im-brow'));
                $i = 1;
            }
            
            $globbuf = $this->imo->output($globbuf, array(
                    'page' => isset($this->im->pagedata['page']) ? 
                            '&page='.$this->im->pagedata['page'] : '',
                    'item-id' => $id,
                    'item-title' => $title,
                    'item-category' => $this->im->items_ordered_struct[$key]['category'],
                    'item-date' =>  $date
            ), true);
            // Prepair visible icon template & link
            $cssclass = 'redoff';           
            if(!isset($this->im->items_ordered_struct[$key]['visible']) || 
               (int)$this->im->items_ordered_struct[$key]['visible'] == 1)
		        $cssclass = 'redon';
            $tmp_vic_o = $this->imo->output($tmp_vic_o, array('visible-class' => $cssclass));
            $globbuf = $this->imo->output($globbuf, array('visible-icon' => $tmp_vic_o));    
             
            // Prepair promo icon template & link
            $cssclass = 'redoff';
            if(!isset($this->im->items_ordered_struct[$key]['promo']) || 
               (int)$this->im->items_ordered_struct[$key]['promo'] == 1)
            {
		        $cssclass = 'redon';
            }
            $tmp_pic_o = $this->imo->output($tmp_pic_o, array('promo-class' => $cssclass));
            $globbuf = $this->imo->output($globbuf, array('promo-icon' => $tmp_pic_o), true);
            
            // buffered tpl
            $tmp_row_o .=  $globbuf;
        }
        return $tmp_row_o;
	}


    /* displays category list inside admin panel */
    private function categoryregister()
    {/*{{{*/
        $tpls = ImModel::getTplKit('categoryregister');
        $cfile = ImModel::getPref();
        $tmp_row_tpl = '';
        $tmp_header_tpl = '';
        $i = 0;
        
        if(!isset($cfile->categories->category[0]) ||
           empty($cfile->categories->category[0]))
            return $this->imo->output($tpls[0], array('value' => $tmp_row_tpl),true,array(),true);

        $tmp_header_tpl = $this->imo->output($tpls[2], array(),true);
        //rows
        foreach($cfile->categories->category as $cat)
        {
            $tmp_row_tpl .= $this->imo->output($tpls[1], array('category' => $cat, 
                'count' => $this->im->items_within_cat($cat)),true);
            if($i > 0)
            {
                $tmp_row_tpl = $this->imo->output($tmp_row_tpl, array('count' => 'im-arow'),true);
                $i = 0;
            } else
            {
                $tmp_row_tpl = $this->imo->output($tmp_row_tpl, array('count' => 'im-brow'),true);
                $i = 1;
            }
        }
        return $this->imo->output($tpls[0], array('value' => $tmp_row_tpl, 'header_row' => $tmp_header_tpl),true,array(),true);
    }/*}}}*/



    /* function that returns the "custom fields" 
       used inside "Create new item" or "Edit item" panel */
    private function customfields($id, $tpls)
    {/*{{{*/
        // include upload Script
        include(GSPLUGINPATH.'imanager/uploadscript/upload.php');
        $fields = $this->im->fields;
        $itemdata = $this->im->itemdata;
        // it's necessary?
        if (!$fields || count($fields) <= 0) 
            return false;
        // define element bundles
        $elements = '';
        $sections = '';
        // define options array
        $options = array();
        // templates rendering section
        foreach ($fields AS $element)
        {
            // copy original templates for easier handling 
            $wrapper     = $tpls[3];
            $hiddenwrapp = '';//$tpls[4];
            $input       = '';//$tpls[5];
            $dropwrapper = '';//$tpls[6];
            $dropdown    = '';//$tpls[7];
            $checkbox    = '';//$tpls[8];
            $area        = '';//$tpls[9];
            $editor      = '';//$tpls[13]
            $hidden      = '';//$tpls[10];
            $uploader    = '';
            $thumb       = '';//$tpls[11];

            // define element attributes
            $key   = strtolower($element['key']);
            $label = $element['label'];
            $type  = $element['type'];

            if(isset($element['options']) && is_array($element['options']))
                $options = $element['options'];

            // get field value 
            $select = !empty($element['value']) ? $element['value'] : '';
            // edit item only
            if (isset($_GET['edit']) && !empty($_GET['edit']))
                $select = $itemdata->$key;
            /*
            * there is the flag type indicator:
            *
            * 0 = default unknow type 
            * 1 = input 
            * 2 = dropbox 
            * 3 = checkbox
            * 4 = textarea
            * 5 = hidden
            * 6 = file upload
            * 7 = editor
            */ 
            $flag = 0;
            switch($type)
            {
                case 'text':
                    $input = $this->imo->output($tpls[5], array('element-key' => stripcslashes('post-'.$key),
                        'element-type' => 'text', 'input-element-class' => 'im-input-text', 
                        'element-id' => $key, 'element-value' => $select),false,array(),true);
                    $flag = 1;
                    break;
                case 'dropdown':

                    foreach($options AS $option)
                    { 
                        $selected = '';
                        if($option == $select)
                            $selected = 'selected';                    
                        $dropdown .= $this->imo->output($tpls[7], array('selected' => $selected, 'value' => $option,
                        'value-text' => $option)); 
                    }
                    $dropwrapper = $this->imo->output($tpls[6], array('element-id' => $key, 'element-key' => 'post-'.$key,
                        'value' => $dropdown),false,array(),true);
                    $flag = 2;
                    break;
                case 'checkbox':
                    $checkbox = $this->imo->output($tpls[8], array('element-id' => $key,
                        'element-name' => 'post-'.$key, 'checked' => $select ? 'checked="checked"' : '',
                        'element-value' => $select),false,array(),true);
                    $flag = 3;
                    break;
                case 'textfull':
                    $area = $this->imo->output($tpls[9], array('element-id' => $key,
                        'element-name' => 'post-'.$key, 'value' => $select),false,array(),true);
                    $flag = 4;
                    break;
                case 'editor':
                    $edprop = array();
                    $edprop = $this->editorproperties($element);
                    $area = $this->imo->output($tpls[13], array('element-id' => $key,
                        'element-name' => 'post-'.$key, 'value' => $select, 'edlanguage' => $edprop['edlang'], 
                        'content-css' => $edprop['csspath'], 'edheight' => $edprop['edheight'], 
                        'siteurl' => ImModel::getProp('paths', 'siteurl'), 'toolbar' => $edprop['toolbar'], 
                        'edoptions' => $edprop['edoptions'], 'setup-editor' => $this->i18n_customfields_customize_ckeditor('editor_1')), false, array(), true);
                    $flag = 7;
                    break;
                case 'hidden':
                    $hidden = $this->imo->output($tpls[10], array('element-id' => $key,
                        'element-key' => 'post-'.$key, 'element-type' => 'hidden', 'value' => $select),false,array(),true);
                    $flag = 5;
                    break;
                case 'uploader':
                    $buff = '';
                    ob_start();
                    genOutput('post-'.$key);
                    $buff = ob_get_clean();
                    try {while (ob_get_level() > 0) ob_end_flush();} catch( Exception $e ) {}
                    $uploader = $this->imo->output($buff, array('element-id' => $key));

                    // thumb
                    if(isset($this->input['edit']) && !empty($this->input['edit']) &&
                       file_exists($select))
                    {
                        $imginfo = @getimagesize($select);
                    
                        $w = $imginfo[0];
                        $h = $imginfo[1];
                     
                        $cfile = ImModel::getPref();
                        $tw = $cfile->item->thumbwidth;
                    
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
                        // thumb width, height, path
                        $thumb = $this->imo->output($tpls[11], array('thumb-w' => $tw,
                            'thumb-h' => $th, 'thumb-path' =>  ImModel::getProp('paths', 'siteurl')
                            . ImModel::getProp('paths', 'uploadpart').basename($select)));
                    } else 
                        $thumb = '';
                        $flag = 6;
                        break;
                }
    
            if($flag > 0) 
            {
                /* replace the placeholder of an element wrpr. by label */
                // ignore our hidden field
                if($flag != 5)
                    $wrapper = $this->imo->output($wrapper, array('label' => $key,
                        'label-text' => stripcslashes($label)));
               
                // text field
                if($flag == 1)
                    $wrapper = $this->imo->output($wrapper, array('value' => stripcslashes($input)));
                // selectbox
                elseif($flag == 2)
                    $wrapper = $this->imo->output($wrapper, array('value' => stripcslashes($dropwrapper)));
                // checkbox
                elseif($flag == 3)
                    $wrapper = $this->imo->output($wrapper, array('value' => $checkbox));
                // textarea and editor
                elseif($flag == 4)
                    $wrapper = $this->imo->output($wrapper, array('value' => stripcslashes($area)));
                // hidden field
                elseif($flag == 5)
                    $wrapper = $this->imo->output('<p>[[value]]</p>', array('value' => $hidden));
                // file uploader
                elseif($flag == 6) {
                    $parts = explode(';', $select);
                    $part = !empty($parts) ? $parts[0] : $select;
                    $wrapper = $this->imo->output($wrapper, array('value' => $thumb.$uploader));
                    $wrapper .= $this->imo->output($tpls[12],array('key' => strtolower($key), 
                                    'select' => $select));
                }// editor field
                elseif($flag == 7)
                    $wrapper = $this->imo->output($wrapper, array('value' => stripcslashes($area)));

            } else
            {
                $wrapper = '';
            }

            $elements .= $wrapper;
        }
        // replace the placeholder by an element wrapper
        return $this->imo->output($tpls[2], array('value' => $elements), true,array(),true);
    }/*}}}*/

    private function editorproperties($ed)
    {
        $edheight = '500px';
        if (defined('GSEDITORHEIGHT'))
            $edheight = GSEDITORHEIGHT .'px';
        $edlang = i18n_r('CKEDITOR_LANG');
        if (defined('GSEDITORLANG')) 
            $edlang = GSEDITORLANG;
        $edtool = 'basic';
        if (defined('GSEDITORTOOL')) 
            $edtool = GSEDITORTOOL;
        $edoptions = '';
        if (defined('GSEDITOROPTIONS') && trim(GSEDITOROPTIONS)!="") 
            $edoptions = ", ".GSEDITOROPTIONS;

        if ($edtool == 'advanced') {
            $toolbar = "
            ['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Table', 'TextColor', 'BGColor', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source'],
            '/',
            ['Styles','Format','Font','FontSize']
            ";
        } elseif ($edtool == 'basic') {
            $toolbar = "['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source']";
        } else {
            $toolbar = GSEDITORTOOL;
        }

        $csspath = '';
        if (isset($TEMPLATE) && file_exists(GSTHEMESPATH . $TEMPLATE .'/editor.css')) 
            $csspath = 'contentsCss: \''. suggest_site_path() .'theme/'. $TEMPLATE .'/editor.css\',';

        /*ob_start();
            ckeditor_add_page_link();
            //exec_action('html-editor-init');
            $result = ob_get_contents();
        ob_end_clean();*/

        return array(
            'edheight' => $edheight, 
            'edlang' => $edlang, 
            'edtool' => $edtool, 
            'edoptions' => $edoptions,
            'toolbar' => $toolbar,
            'csspath' => $csspath,
            /*'resoutput' => $result*/

        );
    }


    private function i18n_customfields_customize_ckeditor($editorvar) { // copied and modified from ckeditor_add_page_link()
        $res = "
        // modify existing Link dialog
        CKEDITOR.on( 'dialogDefinition', function( ev ) {
            if ((ev.editor != " . $editorvar . ") || (ev.data.name != 'link')) return;

            // Overrides definition.
            var definition = ev.data.definition;
            definition.onFocus = CKEDITOR.tools.override(definition.onFocus, function(original) {
                return function() {
                    original.call(this);
                        if (this.getValueOf('info', 'linkType') == 'localPage') {
                            this.getContentElement('info', 'localPage_path').select();
                        }
                };
            });

            // Overrides linkType definition.
            var infoTab = definition.getContents('info');
            var content = getById(infoTab.elements, 'linkType');

            content.items.unshift(['Link to local page', 'localPage']);
            content['default'] = 'localPage';
            infoTab.elements.push({
                type: 'vbox',
                id: 'localPageOptions',
                children: [{
                    type: 'select',
                    id: 'localPage_path',
                    label: 'Select page:',
                    required: true,
                    items: " . $this->i18n_customfields_list_pages_json() . ",
                    setup: function(data) {
                        if ( data.localPage )
                            this.setValue( data.localPage );
                    }
                }]
            });
            content.onChange = CKEDITOR.tools.override(content.onChange, function(original) {
                return function() {
                    original.call(this);
                    var dialog = this.getDialog();
                    var element = dialog.getContentElement('info', 'localPageOptions').getElement().getParent().getParent();
                    if (this.getValue() == 'localPage') {
                        element.show();
                        if (" . $editorvar . ".config.linkShowTargetTab) {
                            dialog.showPage('target');
                        }
                        var uploadTab = dialog.definition.getContents('upload');
                        if (uploadTab && !uploadTab.hidden) {
                            dialog.hidePage('upload');
                        }
                    }
                    else {
                        element.hide();
                    }
                };
            });
            content.setup = function(data) {
                if (!data.type || (data.type == 'url') && !data.url) {
                    data.type = 'localPage';
                }
                else if (data.url && !data.url.protocol && data.url.url) {
                    if (path) {
                        data.type = 'localPage';
                        data.localPage_path = path;
                        delete data.url;
                    }
                }
                this.setValue(data.type);
            };
            content.commit = function(data) {
                data.type = this.getValue();
                if (data.type == 'localPage') {
                    data.type = 'url';
                    var dialog = this.getDialog();
                    dialog.setValueOf('info', 'protocol', '');
                    dialog.setValueOf('info', 'url', dialog.getValueOf('info', 'localPage_path'));
                }
            };
      });</script>";
    }


    private function i18n_customfields_list_pages_json() {
        if (function_exists('find_i18n_url') && class_exists('I18nNavigationFrontend')) {
            $slug = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['newid']) ? $_GET['newid'] : '');
            $pos = strpos($slug, '_');
            $lang = $pos !== false ? substr($slug, $pos+1) : null;
            $structure = I18nNavigationFrontend::getPageStructure(null, false, null, $lang);
            $pages = array();
            $nbsp = html_entity_decode('&nbsp;', ENT_QUOTES, 'UTF-8');
            $lfloor = html_entity_decode('&lfloor;', ENT_QUOTES, 'UTF-8');
            foreach ($structure as $page) {
              $text = ($page['level'] > 0 ? str_repeat($nbsp,5*$page['level']-2).$lfloor.$nbsp : '').cl($page['title']);
              $link = find_i18n_url($page['url'], $page['parent'], $lang ? $lang : return_i18n_default_language());
              $pages[] = array($text, $link);
            }
            return json_encode($pages);
        } else {
            return list_pages_json();
        }
    }


    private function fieldsconfigurator()
    {/*{{{*/
        $tpls = ImModel::getTplKit('fieldsconfigurator');
        $this->im->cfields_setup_restore();
        $fields = $this->im->fields;
        if (!ImCategory::$is_cat_exist) 
            return false;
        
        $buf_tpl = '';

        if($fields == null)
        {
            $buf_tpl = $this->fieldsconfline(0, array(), $tpls[1], 'hidden');
            $o = $this->imo->output($tpls[0], array('categorie_items' => $buf_tpl,
            'cat' => ImCategory::$current_category), true, array(), true);
            return $o;
        }
       
        // build our fields + hidden field
        $i = 0;
        foreach($fields as $def) {
            $buf_tpl .= $this->fieldsconfline($i++, $def, $tpls[1], 'sortable');
        }
        $buf_tpl .= $this->fieldsconfline($i++, array(), $tpls[1], 'hidden');


        $o = $this->imo->output($tpls[0], array('categorie_items' => $buf_tpl,
            'cat' => ImCategory::$current_category), true, array(), true);
        return $o;
    }/*}}}*/
    


    /* conf line output */
    private function fieldsconfline($i, $def, $tpl, $class = '')
    {/*{{{*/
        $isdropdown = false;
        if(isset($def['type']) && $def['type'] == 'dropdown')
            $isdropdown = $def['type'];
        $options = "\r\n";
        if ($isdropdown && count($def['options']) > 0) 
        {
            foreach ($def['options'] as $option) 
                $options .= $option . "\r\n";

            $tpl = $this->imo->output($tpl, array('area-options' => $options));
        }
        return $this->imo->output($tpl, array('tr-class' => $class,
            'i' => $i, 'key' => isset($def['key']) ? $def['key'] : '',
            'label' => isset($def['label']) ? $def['label'] : '',
            'selected-text' => @$def['type']=='text' ? 'selected="selected"' : '',
            'selected-longtext' => @$def['type']=='textfull' ? 'selected="selected"' : '',
            'selected-dropdown' => @$def['type']=='dropdown' ? 'selected="selected"' : '',
            'selected-checkbox' => @$def['type']=='checkbox' ? 'selected="selected"' : '',
            'selected-editor' => @$def['type']=='editor' ? 'selected="selected"' : '',
            'selected-hidden' => @$def['type']=='hidden' ? 'selected="selected"' : '',
            'selected-file' => @$def['type']=='uploader' ? 'selected="selected"' : '',
            'area-display' => !$isdropdown ? 'display:none' : '',
            'text-options' => @$def['value']),true
        );
    }/*}}}*/



    /* outputs pagination */
    public function paginator($tpls)
    {/*{{{*/
        // limit per page
        $limit = $this->im->pagedata['limit'];
        // adjacent
	    $adjacents = $this->im->pagedata['adjacents'];
        // last page
        $lastpage = $this->im->pagedata['lastpage'];
        // current
	    $page = $this->im->pagedata['page'];
        // first page
        $start = $this->im->pagedata['start'];
        // next page
        $next = $this->im->pagedata['next'];
        // preview page
	    $prev = $this->im->pagedata['prev'];
	    //$next = $page + 1;
        $lpm1 = $this->im->pagedata['lpm1'];
        // url 
        $pageurl = $this->im->pagedata['pageurl'];
        /* Ok, now generate our paginator output */
        $o = '';
        if($lastpage > 1)
        {
            //previous button
            if($page > 1)
                $o .= $this->imo->output($tpls[1], array('link-href' => $pageurl.$prev), true); 
            else
                $o .= $this->imo->output($tpls[4], array(), true);	
            
            //pages	
            if($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
            {	
                for($counter = 1; $counter <= $lastpage; $counter++)
                {
                    if($counter == $page)
                    {
                        $o .= $this->imo->output($tpls[6], array('counter' => $counter), true);
                    } else 
                    {
                        $tmp_o = $this->imo->output($tpls[3], array('link-href' => $pageurl.$counter), true);
                        // pass as a string
                        $o .= $this->imo->output($tmp_o, array('counter' => $counter), true); 
                    }
                }
            // enough pages to hide some
            } elseif($lastpage > 5 + ($adjacents * 2))
            {
                // vclose to beginning; only hide later pages
                if($page < 1 + ($adjacents * 2))		
                {
                    for($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                    {
                        if($counter == $page)
                        {
                            $o .= $this->imo->output($tpls[6], array('counter' => $counter), true);
                        } else
                        {
                            $tmp_o = $this->imo->output($tpls[3], array('link-href' => $pageurl.$counter), true);
                            // pass as a string 
                            $o .= $this->imo->output($tmp_o, array('counter' => $counter), true);
                        }
                    }
                    // ...
                    $o .= $this->imo->output($tpls[7]);
                    // sec last
                    $tmp_o = $this->imo->output($tpls[8], array('link-href' => $pageurl.$lpm1), true);
                    $o .= $this->imo->output($tmp_o, array('counter' => $lpm1), true);
                    // last
                    $tmp_o = $this->imo->output($tpls[9], array('link-href' => $pageurl.$lastpage), true);
                    $o .= $this->imo->output($tmp_o, array('counter' => $lastpage), true);
                }
                // middle pos; hide some front and some back
                elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
                {
                    // first
                    $o .= $this->imo->output($tpls[10], array('link-href' => $pageurl.'1'), true);
                    // second
                    $o .= $this->imo->output($tpls[11], array('link-href' => $pageurl.'2'), true);
                    // ...
                    $o .= $this->imo->output($tpls[7]);

                    for($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                    {
                        if($counter == $page)
                        {
                            $o .= $this->imo->output($tpls[6], array('counter' => $counter), true);
                        } else
                        {
                            $tmp_o = $this->imo->output($tpls[3], array('link-href' => $pageurl.$counter), true);
                            $o .= $this->imo->output($tmp_o, array('counter' => $counter), true);
                        }
                    }
                    // ...
                    $o .= $this->imo->output($tpls[7]);
                    // sec last
                    $tmp_o = $this->imo->output($tpls[8], array('link-href' => $pageurl.$lpm1), true);
                    $o .= $this->imo->output($tmp_o, array('counter' => $lpm1), true);
                    // last
                    $tmp_o = $this->imo->output($tpls[9], array('link-href' => $pageurl.$lastpage), true);
                    $o .= $this->imo->output($tmp_o, array('counter' => $lastpage,$link_last_tpl), true);
                }
                //close to end; only hide early pages
                else
                {
                    // first
                    $o .= $this->imo->output($tpls[10], array('link-href' => $pageurl.'1'), true);
                    // second
                    $o .= $this->imo->output($tpls[11], array('link-href' => $pageurl.'2'), true);
                    // ...
                    $o .= $this->imo->output($tpls[7]);

                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
                    {
                        if ($counter == $page)
                        {
                            $o .= $this->imo->output($tpls[6], array('counter' => $counter), true);
                        } else
                        {
                            $tmp_o = $this->imo->output($tpls[3], array('link-href' => $pageurl.$counter), true);
                            $o .= $this->imo->output($tmp_o, array('counter' => $counter), true);
                        }
                    }
                }
            }   
            //next link
            if($page < $counter - 1) 
                $o .= $this->imo->output($tpls[2], array('link-href' => $pageurl.$next), true);
            else
                $o .= $this->imo->output($tpls[5], array(), true);
        }
        return $this->imo->output($tpls[0], array('value' => $o), true);
    }/*}}}*/


    /* method to display settings menu */
    private function setupdisplay()
    {
        //$tpls = ImModel::getTplKit('preferencer');
        $tpls = array_merge(ImModel::getTplKit('preferencer'), ImModel::getTplKit('imajax'));
        //var_dump($tpls);
        $cfile = ImModel::getPref();
        $tvs = '';
        /*$tpls = ImModel::getTplKit('fieldsconfigurator');
        $this->im->cfields_setup_restore();
        $fields = $this->im->fields;
        var_dump($fields);*/

        if(empty($cfile->categories->category[0]))
            return '';
        foreach($cfile->categories->category as $cat)
        {
            $tag = (ImCategory::$current_category == $cat) ? 'selected' : '';
            $tvs .= $this->imo->output($tpls[1], array('selected' => $tag, 
                'option-value-cat-selector' => $cat), true);
        }
        //option-cat-select
        
        return $this->imo->output($tpls[0], 
            array(
                'option-cat-select' => $tvs, 
                'items-per-page' => $cfile->item->itemsperpage,
                'imajax-tpl' => $this->imo->output($tpls[3], 
                	array('baseurl' => ImModel::getProp('paths', 'siteurl'))
                ),
                'uri-add' => isset($this->input['fields']) ? '&fields' : ''
            ), 
            true, array(), true
        ); 
        //var_dump(ImModel::getPref());
    }




    private function renametool()
    {
        $tpls = ImModel::getTplKit('renametool');
        // check c.fields exists
        $this->im->cfields_setup_restore();
        $fields = $this->im->fields;
        if (!$fields) 
            return false;

        return $this->imo->output($tpls, array('formaction' => curPageURL(),
                    'cat' => ImCategory::$current_category), true);
    }


    /* displays messages */
    private function msg()
    {/*{{{*/
        $o = '';
        $msg = ImMsgReporter::msgs();
        if(!empty($msg))
            foreach($msg as $val)
                $o .= $val;
        return $o;
    }/*}}}*/


    /* displays category selector form */
    private function catselector()
    {
        $tpls = array_merge(ImModel::getTplKit('catselector'), 
        	ImModel::getTplKit('imajax')
        );
        $cfile = ImModel::getPref();
        $tvs = '';
        if(empty($cfile->categories->category[0]))
            return '';
        foreach($cfile->categories->category as $cat)
        {
            $tag = (ImCategory::$current_category == $cat) ? 'selected' : '';
            $tvs .= $this->imo->output($tpls[1], array('selected' => $tag, 'catvalue' => $cat), true);
        }

        // output category selector form
        return $this->imo->output($tpls[0], array('catoptions' => $tvs, 
            'uri-add' => isset($this->input['fields']) ? '&fields' : '',
            // load ajax section
            'imajax' => $this->imo->output($tpls[2], 
                	array('baseurl' => ImModel::getProp('paths', 'siteurl'))
            ),
         ),true, array(), true);
    }


    private function output($values)
    {/*{{{*/
        $tpl = ImModel::getTplKit('output');
        foreach($values as $key => $val)
             $tpl = $this->imo->output($tpl, array($key => $val));
        return $tpl;
    }/*}}}*/    

}
