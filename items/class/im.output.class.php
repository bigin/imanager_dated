<?php
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*/

/**
* Administration of the output and HTML templates 
*
*
*/
class ImOtput
{
    // templates array
    private $tpls;
    // reporter object
    private $r;

    public function __construct($path, $reporter)
    {
        $this->tpls['names'] = $this->item_pages = glob($path.'*.xml');
        if(!empty($this->tpls['names']))
            foreach($this->tpls['names'] as $i)
                $this->tpls['contents'] = file_get_contents($i);
    
        $this->r = $reporter;
    }

    
    public function output($tpl, $dep = '')
    {
        switch($dep) 
        {
            case 0:
                echo "i equals 0";
                break;
            case 1:
                echo "i equals 1";
                break;
            case 2:
                echo "i equals 2";
                break;
            default:
                if(is_tpl_single($tpl))
                {
                    $this->r->langRestubsTpl($tpl);
                    return $this->r->tpls[$tpl];
                }  
        }
    }


    private function is_tpl_single($tpl)
    {
        
    }
    
}
?>
