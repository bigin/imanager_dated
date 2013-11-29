<?php
/**
* ImCategory class 
*
* Categories administration
*
**/
class AmGroups
{
    private static $p;
    public static $is_group_valid;
    public static $current_group;
    public static $is_group_exist;

    public function __construct($preferences)
    {
        self::$p = $preferences;
        if(self::$is_cat_valid = self::is_cat_valid())
        {
            self::$is_cat_exist = true;
            self::$current_category = $this->current_category();
        } else
        {
            if(!empty(self::$p->categories->category[0]))
            {
                self::$is_cat_exist = true;
                self::$current_category = 
                    safe_slash_html_input(self::$p->categories->category[0]);
            } else
            {
                self::$is_cat_exist = false;
                self::$current_category = '';
            }
        }
    }

    public static function setCategory($newcat)
    {
        foreach(self::$p->categories->category as $cat)
            if($cat == safe_slash_html_input($newcat))
            {
                self::$is_cat_exist = true;
                self::$current_category = $cat;
                return true;
            } 
        return false;
    }

    private function current_category()
    {
        return safe_slash_html_input($_REQUEST['cat']);
    }

    public static function is_cat_valid($cq='')
    {
        if(isset($cq) && !empty($cq))
            foreach(self::$p->categories->category as $cat)
                if($cat == safe_slash_html_input($cq))
                    return true;

        if(!isset($_REQUEST['cat']) || empty($_REQUEST['cat']))
            return false;
        foreach(self::$p->categories->category as $cat)
            if($cat == safe_slash_html_input($_REQUEST['cat']))
                return true;
        return false;
    }

}
?>
