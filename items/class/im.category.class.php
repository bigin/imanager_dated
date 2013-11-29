<?php
/**
* Plugin Name: Item Manager
* Description: Full Featured Items Manager.
* Version: 0.5 Beta
* Author: Bigin (modified PyC's plugin ) 
* Author URI: http://www.ehret-studio.de
*
*
* ImCategory class 
*
* Categories administration
*
**/

class ImCategory
{
    // category indicator
    public static $iscategory = false;

    public function __construct($path)
    {
        $category_file = getXML($path);
        if(!empty($category_file->categories->category[0]))
            self::$iscategory = true;
    }

    public function catSelector($Reporter, $noun = 'custom fields')
    {   
        global $ISCAT;
        $Reporter->langRestubsTpl('configure.menu.selector.init.edit.tpl');
    // load category selector wrapper tpl
    $conf_menu_cat_tpl = $Reporter->tpls['configure.menu.selector.init.edit.tpl'];
    $Reporter->langRestubsTpl('configure.menu.element.selector.init.edit.tpl');
    // load category element tpl
    $conf_menu_cat_ele_tpl = $Reporter->tpls['configure.menu.element.selector.init.edit.tpl'];

    $category_file = getXML(ITEMDATAFILE);

    if(count($category_file->categories->category) < 1)
    {
        return preg_replace('%\[\[( *)noun( *)\]\]%', $noun, $Reporter->getClause('items/no_category_created'));
    } else 
    {
        $ISCAT = true;
        $curcat = '';
        $temp_cat_tpl = '';
        foreach($category_file->categories->category as $cat)
        {
            $sel = (isset($_POST['cat']) && $_POST['cat'] == $cat) ? 'selected' : '';
            $temp_cat_tpl .= preg_replace(
                '%\[\[( *)selected( *)\]\]%', 
                $sel, 
                $conf_menu_cat_ele_tpl
            );
            $temp_cat_tpl = preg_replace(
                '%\[\[( *)catvalue( *)\]\]%', 
                $cat, 
                $temp_cat_tpl
            );
        }

        $conf_menu_cat_ele_tpl = $temp_cat_tpl;

        $conf_menu_cat_tpl = preg_replace(
            '%\[\[( *)catoptions( *)\]\]%', 
            $conf_menu_cat_ele_tpl, 
            $conf_menu_cat_tpl
        );
        return $conf_menu_cat_tpl;
    }
    }
}
?>
