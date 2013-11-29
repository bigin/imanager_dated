<?php
class ImFieldsConfigurator
{
    public $_invalid_names=null;
    private $cf;
    private $bcf;
    private $post;
    private $get;


    public function __construct($custom_file, $back_custom_file)
    {
        $this->cf = $custom_file;
        $this->bcf = $back_custom_file;
        $this->post = isset($_POST) ? $_POST : null;
        $this->get = isset($_GET) ? $_GET : null;
        if(!file_exists($custom_file) && ImCategory::$is_cat_exist)
        {
           	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
	        $xml->asXML($custom_file);
        }
    }


    public function save_preprocessor()
    { 
        $this->invalid_names = $this->invalid_names();
        if(!$this->_invalid_names && $this->save_fields()) 
            return 1;
        
        if($this->invalid_names)
            return 0;
        else
           return -1; 
    }


    public function unlink_customfiels_file($file)
    {
        if(file_exists($file))
            unlink($file);
    }

    private function save_fields() 
    {
        if(!copy($this->cf, $this->bcf))
            return false;

 	    $data = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
        for($i=0; isset($this->post['cf_'.$i.'_key']); $i++) {
            if ($this->post['cf_'.$i.'_key']) {
                $item = $data->addChild('item');
                $item->addChild('desc')->addCData(htmlspecialchars(stripslashes($this->post['cf_'.$i.'_key']), ENT_QUOTES));
                $item->addChild('label')->addCData(htmlspecialchars(stripslashes($this->post['cf_'.$i.'_label']), ENT_QUOTES));
                $item->addChild('type')->addCData(htmlspecialchars(stripslashes($this->post['cf_'.$i.'_type']), ENT_QUOTES));
                if($this->post['cf_'.$i.'_value']) {
                    $item->addChild('value')->addCData(htmlspecialchars(stripslashes($this->post['cf_'.$i.'_value']), ENT_QUOTES));
                }
                if ($this->post['cf_'.$i.'_options']) {
                    $options = preg_split("/\r?\n/", rtrim(stripslashes($this->post['cf_'.$i.'_options'])));
                    foreach ($options as $option) {
                        $item->addChild('option')->addCData(htmlspecialchars($option, ENT_QUOTES));
                    }
                }
            }
        }
 	    XMLsave($data, $this->cf);
        return true;
    }

    private function invalid_names() 
    {
        $stdfields = array();
        $names = array();
        for ($i=0; isset($this->post['cf_'.$i.'_key']); $i++) 
        {
            if (in_array($this->post['cf_'.$i.'_key'], $stdfields)) 
                $names[] = $this->post['cf_'.$i.'_key'];
        }
        return count($names) > 0 ? $names : null;
    }
}
