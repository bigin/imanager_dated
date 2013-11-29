<?php
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/
class IMreporter
{
    private $tplPath;
    public $tpls;
    private $_lang;

    public function __construct($path = '')
    {
         $this->tplPath = GSPLUGINPATH.'items/templates/';
         if(!empty($path))
            $this->tplPath = $path;
         $this->_lang = array();
    }

    public function getClause($clause, $var = array())
    {
        i18n_merge('items') || i18n_merge('items','en_US'); 
        
        $output = i18n_r($clause); 
        if(empty($var))
            return $output;
        foreach($var as $key => $value)
            $output = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $output);
        return $output;
    }

    /* The $docKeyTpl param can be a "template file name", or "template string" */
    public function langRestubsTpl($docKeyTpl, $docValue='', array $tvs=array())
    {
        if(empty($docValue))
        {
            if(!file_exists($this->tplPath.$docKeyTpl))
                return false;
            if(!$this->tpls[$docKeyTpl] = file_get_contents($this->tplPath.$docKeyTpl))
                return false;
        } else {
            $this->tpls[$docKeyTpl] = $docValue;
        }
        
        if(empty($tvs))
        { 
            global $i18n;
            $this->_lang = $i18n;
        } else 
        {
            $this->_lang = $tvs;
        }
        

        foreach($this->_lang as $key => $value)
        {
            if (strpos($key, $this->tpls[$docKeyTpl]) !== true) 
			    $this->tpls[$docKeyTpl] = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $this->tpls[$docKeyTpl]);
        }
                
    }
}

?>
