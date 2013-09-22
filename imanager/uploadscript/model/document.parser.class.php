<?php
/** Simple Parser Methods
*   The Template Parser Class enables you to parse 
*   pseudo-variables contained within your tpl files.
*
*   Stdandard-TV's
*   Language-TV's
*   Template-TV's (to integrate been parsed templates) */
class documentParser 
{
    protected static function registerDoc($docKeyTpl, $docValueTpl) 
    {
    $upload = uploadMain::getInstance();

        return $upload->setPropValue(TEMPLATE_PROP, strtolower($docKeyTpl), 
                                 file_get_contents($docValueTpl), 'tpls');
    }

    protected static function parseTV($docKeyTpl, array $tvs = array()) 
    {
        $upload = uploadMain::getInstance();

        if(!$upload->getPropValue(TEMPLATE_PROP, $docKeyTpl, 'tpls'))
            return false;   
        foreach ($tvs as $key => $value) {
            // Replace template vars
            $upload->setPropValue(TEMPLATE_PROP, strtolower($docKeyTpl), str_replace(
                                  '[[+'.$key.']]', $value, $upload->getPropValue(
                                  TEMPLATE_PROP, strtolower($docKeyTpl), 'tpls')), 'tpls');
        }
        foreach ($upload->getPropValue(LANGUAGE_PROP) as $key => $value) {
            // Replace labels & desciptions
            $upload->setPropValue(TEMPLATE_PROP, strtolower($docKeyTpl), str_replace(
                                  '[[+'.$key.']]', $value, $upload->getPropValue(
                                  TEMPLATE_PROP, strtolower($docKeyTpl), 'tpls')), 'tpls');
        }
        foreach ($upload->getPropValue(TEMPLATE_PROP, 'tpls') as $key => $value) {
            // Replace templates
            $upload->setPropValue(TEMPLATE_PROP, strtolower($docKeyTpl), str_replace(
                                  '[[+'.$key.']]', $value, $upload->getPropValue(
                                  TEMPLATE_PROP, strtolower($docKeyTpl), 'tpls')), 'tpls');
        }
        // Remove empty HTML atributes like: name="", class="", etc..
        foreach($upload->getPropValue(SYSTEM_PROP, 'systvs') as $value) {
            $upload->setPropValue(TEMPLATE_PROP, strtolower($docKeyTpl), preg_replace(
                                  '/( *'.$value.' *= *"" *)/', ' ', $upload->getPropValue(
                                  TEMPLATE_PROP, strtolower($docKeyTpl), 'tpls')), 'tpls');
            
        }
        // Clean placeholders by non-existent values 
        $upload->setPropValue(TEMPLATE_PROP, strtolower($docKeyTpl), preg_replace(
                              '/(\[\[\+.*?\]\])/', '', $upload->getPropValue(
                              TEMPLATE_PROP, strtolower($docKeyTpl), 'tpls')), 'tpls');

        return true;
    }
}
?>
