<?php
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
        $_lang = $tvs;
        if(empty($_lang))
            if(!$_lang = $this->imanager_i18n('imanager')) 
                $_lang = $this->imanager_i18n('imanager','en_US');  

        foreach($_lang as $key => $value)
        {
            if(strpos($key, $tpl) !== true) { 
			    $tpl = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $tpl);
            }
        }
        return $tpl;
    }


     /** returns language array  */
    private function imanager_i18n($plugin, $language=null) 
    {
        $lp = array();
        global $LANG;
        if($this->imanager_prep_i18n($plugin, $language ? $language : $LANG, $lp))
            return $lp;
        return false;
    }


    /** prepairs language array */
    private function imanager_prep_i18n($plugin, $lang, &$lp) 
    { 
        $i18n = array();
        if (!file_exists(GSPLUGINPATH.$plugin.'/lang/'.$lang.'.php')) 
        {
  	        return false;
        }
        // bug in PHP functionality's been missing since at least 2006 
        @include(GSPLUGINPATH.$plugin.'/lang/'.$lang.'.php'); 
        if (count($i18n) > 0)
        {
            foreach ($i18n as $code => $text) 
            {
                if (!array_key_exists($plugin.'/'.$code, $lp)) 
                {
    	            $lp[$plugin.'/'.$code] = $text;
                }
            }
            return true;
        }
        return false;
    }
}
?>
