<?php
class AmController 
{
    private $am;
    private $amo;
    private $input;

    public function __construct($input=array())
    {
        /* input $_GET and $_POST */
        $this->input = !empty($input) ? $input : array_merge($_GET, $_POST);
        // initialise the classes
        $this->am = new AmModel($this->input);
        $this->amo = new AmOutput();    
    }


    public function displayBackEnd()
    {

        $o = array('header' => '', 'msg' => '', 'selector' => '', 'content' => '' );
        // firstly, check Category-Reloader request
        if(isset($this->input['reloader']) && isset($this->input['post-access_key']))
        {
            // set category and regrab item data
            #ImCategory::setCategory($this->input['post-category']);
        }
        // generate tab panel output
        $o['header'] = $this->tabpanel();
        // check error messages
        $msg = AmMsgReporter::msgs();
        // output edit item menu
        if(isset($this->input['edit']))  
        {
                if(ImCategory::$is_cat_exist)
                $o['content'] = $this->itemeditor();
	    }
        /*
        // delete item
	    elseif (isset($this->input['delete']))
	    {
            if($this->im->item_delete($this->input['delete']))
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemregister();
            }
        } 
        // change visibility
	    elseif (isset($this->input['visible'])) 
	    {
		    if($this->im->visibility_change())
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemregister();
            }
	    }
        // change promotion 
	    elseif (isset($this->input['promo'])) 
	    {
		    if($this->im->promotion_change())
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemregister();
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
		    // todo tlz.bh
	    } 
        // save item
        elseif (isset($this->input['submit'])) 
	    {      
		    if(!$this->im->saveitem())
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemregister();
            } else 
            {
                if(ImCategory::$is_cat_exist)
                    $o['content'] = $this->itemregister();
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
	    }*/
        // show item list menu
	    /*elseif(!AmModel::$setup && !$msg)
	    {
            #$o['content'] = $this->itemregister();
	    }*/

#if(!isset($this->input['edit']) && !isset($this->input['category']))
#            $o['selector'] = $this->catselector();
        $o['msg'] = $this->msg();
        return $this->output($o);
    }



    private function tabpanel() 
    {/*{{{*/
        // grab tab panel template
        $tpl = $this->am->getPropertyArray('SELECT content FROM templates 
                WHERE context=? AND title=?','backend','header'
        );
        // label names 
        $tabs = $this->am->getPropertyArray('SELECT value FROM settings 
                WHERE key=?','admin_tab_items'
        );

        $tvs = array();
        //var_dump($tabs[0]['value']);
        $labels = $this->am->toArray($tabs[0]['value']);

        $f = false;
        //if(isset($this->input['reloader']))
        //    $this->input['edit'] = '';
        foreach($labels as $label)
        {
            if(isset($this->input[$label]))
            {
                $tvs[$label] = 'class = "current"';
                $f = true;
                break;
            }
        }
        // colorate "viev all" menu point
        if(!$f)
            $tvs['view'] = 'class = "current"';
        $tvs['itemmanager-title'] = IMTITLE;
        // output header
        return $this->amo->output($tpl[0]['content'], $tvs, true, array(), true);/*}}}*/
    }


    /* displays messages */
    private function msg()
    {/*{{{*/
        $o = '';
        $msg = AmMsgReporter::msgs();
        if(!empty($msg))
            foreach($msg as $val)
                $o .= $val;
        return $o;
    }/*}}}*/


    private function output($values)
    {/*{{{*/
        $tpl = $this->am->getPropertyArray('SELECT content FROM templates 
                WHERE context=? AND title=?','backend','outputorder'
        );
        $o = $tpl[0]['content'];
        foreach($values as $key => $val)
             $o = $this->amo->output($o, array($key => $val));
        return $o;
    }/*}}}*/    
    
}
?>
