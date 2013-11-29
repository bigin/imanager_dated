<?php
define ('SYSTEM_PROP',   0);
define ('LANGUAGE_PROP', 1);
define ('TEMPLATE_PROP', 2);
define ('FILTER_PROP',   3);
define ('ERROR_PROP',    4);
require_once('document.parser.class.php');
// Model
class uploadMain extends documentParser
{
    
    private static $instance = null;
    private $uploadProp = array();
    public $docParser = null;
        
    private function __construct(array $lang, array $sysprop, array $tplprop, array $fltprop) 
    {

        $this->uploadProp[LANGUAGE_PROP] = $lang;
        $this->uploadProp[SYSTEM_PROP] = $sysprop;
        $this->uploadProp[TEMPLATE_PROP] = $tplprop;
        $this->uploadProp[FILTER_PROP] = $fltprop;
        $this->uploadProp[ERROR_PROP]['indicator']      = false;
        $this->uploadProp[ERROR_PROP]['value']          = '';
        $this->uploadProp[ERROR_PROP]['level']          = 0;
    }
    
    public static function getInstance(array $lang = array(), 
                                       array $sysprop = array(), 
                                       array $tplprop = array(), 
                                       array $fltprop = array())
    {
        if (self::$instance === null) {
            if(count($lang)==0 || count($sysprop)==0 || count($tplprop)==0 || count($fltprop)==0)
                exit('Error: Parameter expected');
            self::$instance = new self($lang, $sysprop, $tplprop, $fltprop);
            self::$instance->docParser = new documentParser();
        }
        return self::$instance;
    }

    public function callParentMethod($method, $argo, $argt)
    {
        return parent::$method($argo, $argt);
    }

    public function setPropValue($section, $key, $value, $parentKey = NULL) 
    {
        $errBuff = '';
        if(isset($parentKey)) {
            if(isset($this->uploadProp[$section])) {
                $this->uploadProp[$section][$parentKey][$key] = $value;
                return true;
            }
        } else {
            if(isset($this->uploadProp[$section])) {
                $this->uploadProp[$section][$key] = $value;
                return true;
            }
        }   
        $errBuff = sprintf($this->uploadProp[LANGUAGE_PROP]['err_noparam'], '['.$key.']');
        if(isset($parentKey))
            $errBuff = sprintf($this->uploadProp[LANGUAGE_PROP]['err_noparam'], 
                        '['.$parentKey.']['.$key.']');    
        $this->uploadProp[ERROR_PROP]['indicator'] = true; 
        $this->uploadProp[ERROR_PROP]['value'] = $errBuff;
        $this->uploadProp[ERROR_PROP]['level'] = 1;
        return false;
    }
    
    public function getPropValue($section, $key = NULL, $parentKey = NULL) 
    {
        $errBuff = '';
        if(isset($parentKey)) {
            if(isset($this->uploadProp[$section][$parentKey][$key]))
                return $this->uploadProp[$section][$parentKey][$key];
        } else if (isset($key)) {
            if(isset($this->uploadProp[$section][$key]))
                return $this->uploadProp[$section][$key];
        } else {
            if(isset($this->uploadProp[$section]))
                return $this->uploadProp[$section];
        }

        $errBuff = sprintf($this->uploadProp[LANGUAGE_PROP]['err_noparam'], '['.$section.']');
        if(isset($parentKey))
            $errBuff = sprintf($this->uploadProp[LANGUAGE_PROP]['err_noparam'], 
                        '['.$section.']['.$parentKey.']['.$key.']');
        else if(isset($key))
            $errBuff = sprintf($this->uploadProp[LANGUAGE_PROP]['err_noparam'], 
                        '['.$section.']['.$key.']');

        $this->uploadProp[ERROR_PROP]['indicator'] = true; 
        $this->uploadProp[ERROR_PROP]['value'] = $errBuff;
        $this->uploadProp[ERROR_PROP]['level'] = 1;

        return false;    
    }

    private function __clone(){}
}

?>
