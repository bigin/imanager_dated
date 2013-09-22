<?php
class ImMsgReporter
{
    private static $_msgs=array();

    public static function setClause($name, array $var=array())
    {
        i18n_merge('imanager') || i18n_merge('imanager','en_US'); 
        $o = i18n_r('imanager/'.$name); 
        if(empty($var)) 
        {
            self::$_msgs[] = $o;
            return;
        }
        foreach($var as $key => $value)
            $o = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $o);
        self::$_msgs[] = $o;
    }

    public static function getClause($name, array $var=array())
    {
        i18n_merge('imanager') || i18n_merge('imanager','en_US'); 
        $o = i18n_r('imanager/'.$name); 
        if(empty($var))
            return $o;
        foreach($var as $key => $value)
            $o = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $o);
        return $o;
    }

    public static function msgs(){return (self::$_msgs);}  
}
?>
