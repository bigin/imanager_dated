<?php
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de 
*
*/
/* ~View~ */
class ImOutput
{
    // templates array
    private $tpls;

    public function __construct($path)
    {
        $buf = glob($path.'*.tpl');
        if(!empty($buf))
            foreach($buf as $i)
            {
                $this->tpls['fullpaths'][basename($i)] = $i;
                $this->tpls['names'][basename($i)] = basename($i);
                $this->tpls['prefixes'][basename($i)] = basename($i, '.tpl');
                $this->tpls['contents'][basename($i)] = file_get_contents($i);
            }
    }

    /**
    * 
    * @tpl - official template name or string
    * @tvs - template variables 
    * @audit - check for language placeholders 
    * @avs - language variables
    * @clean - clean template 
    */
    public function output($tpl, array $tvs=array(), 
            $audit=false, array $avs=array(), $clean=false)
    {   
        if(isset($this->tpls['contents'][$tpl]) && 
           !empty($this->tpls['contents'][$tpl]))
        {
            $o = $this->tpls['contents'][$tpl];
            if($audit)
            {
                $o = $this->langAuditTpl($o, $avs); 
            }
        } else 
        {
            // if $tpl passed as a string
            $o = $tpl;
            if($audit)
            {
                $o = $this->langAuditTpl($o, $avs); 
            }
        }

        if(!empty($tvs))
            foreach($tvs as $key => $val)
                $o = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $val, $o);

        if($clean)
            return preg_replace('%\[\[(.*)\]\]%', '', $o);         
        return $o;
    }
    

    /** replace language placeholders */
    private function langAuditTpl($tpl, array $tvs=array())
    {   
        if(empty($tvs))
        {
            // get messages
            i18n_merge('imanager') || i18n_merge('imanager','en_US');
            global $i18n;
            $_lang = $i18n;
        } else 
        {
            $_lang = $tvs;
        }

        foreach($_lang as $key => $value)
        {
            if(strpos($key, $tpl) !== true) 
			    $tpl = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $tpl);
        }
        return $tpl;
    }
}
?>
