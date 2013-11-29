<?php
/* ~View~ */
class AmOutput
{
    /**
    * 
    * @tpl - template string
    * @tvs - template variables 
    * @audit - check for language placeholders 
    * @avs - language variables
    * @clean - clean template 
    */
    public function output($tpl, array $tvs=array(), 
            $audit=false, array $avs=array(), $clean=false)
    {   
        $o = $tpl;
        if($audit)
            $o = $this->langAuditTpl($tpl, $avs); 

        if(!empty($tvs))
            foreach($tvs as $key => $val)
                $o = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $val, $o);

        if($clean)
            return preg_replace('%\[\[(.*)\]\]%', '', $o);         
        return $o;
    }
    

    /** replace language placeholders */
    private function langAuditTpl($tpl, array $tvs=array())
    {   // get language  
        $_lang = $tvs;
        if(empty($_lang))
            if(!$_lang = $this->amanager_i18n('amanager')) 
                $_lang = $this->amanager_i18n('amanager','en_US');

        foreach($_lang as $key => $value)
            if(strpos($key, $tpl) !== true) 
			    $tpl = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $tpl);

        return $tpl;
    }


    /** returns language array  */
    private function amanager_i18n($plugin, $language=null) 
    {
        $lp = array();
        global $LANG;
        if($this->amanager_prep_i18n($plugin, $language ? $language : $LANG, $lp))
            return $lp;
        return false;
    }


    /** prepair language array */
    private function amanager_prep_i18n($plugin, $lang, &$lp) 
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
