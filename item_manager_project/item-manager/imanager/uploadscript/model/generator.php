<?php

function prepDataFormDisplay(&$tpl, &$sys)
{
    $tpl['tpls'] = array();
    
    // Modify our window url parameter
    $tpl['formtemplate']['upload_script_url'] = 
                'http://'.$_SERVER['SERVER_NAME'].'/'.str_replace($_SERVER['DOCUMENT_ROOT'], '', 
                $tpl['formtemplate']['upload_script_url']);
    
    // Convert $sys['systvs'] to an array and 
    // prepare them for further processing
    $arrbuff = explode(',', $sys['systvs']);
    $sys['systvs'] = array();
    foreach($arrbuff as $value)
        $sys['systvs'][] = trim($value);
 
    $tpl['formtemplate']['form_input_name'] = $sys['form_input_name'];
}


function prepDataWinDisplay(&$tpl, &$sys)
{
    $tpl['tpls']           = array();
    $tpl['windowjsheader'] = array();
    $tpl['windowjsbody']   = array();

    // Check user by IP and generate an user-ident, we'll 
    // need it later to generate an user specified layout
    // to display the thumbnails instead of the full-size images
    $sys['uid'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
        htmlspecialchars($_SERVER['HTTP_X_FORWARDED_FOR']) :
        htmlspecialchars($_SERVER['REMOTE_ADDR']);
    // Check if Session authentification is `On`
    if(!empty($sys['sess_auth']) && strtolower($sys['sess_auth']) == 'on') {
        session_start();
        if(empty($_SESSION['uid'])) {
            $_SESSION['uid'] = '';
            $charset = 'abcdefghijklmnopqrstuvwxyz'.
                       'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                       '0123456789.-';

            for($i=0; $i < 4; $i++)
                $_SESSION['uid'] .= $charset[mt_rand(0,strlen($charset))];
        }
        $sys['uid'] .= $_SESSION['uid'];
    }
    // Define javaScript couter 
    $tpl['windowjsbody']['js_counter'] = '';
    // Define other values these will automatically 
    // be completed during the script execution
    $tpl['windowimageloop']['imgname_bg']  = '';
    $tpl['windowimageloop']['tmpurl']      = '';
    $tpl['windowimageloop']['image']       = '';
    $tpl['windowimageloop']['stdname']     = '';
    $tpl['windowimageloop']['js_counter']  = '';
    $tpl['windowimageloop']['href_delete'] = '';

    // Convert $sys['systvs'] to an array and 
    // prepare them for further processing
    $arrbuff = explode(',', $sys['systvs']);
    $sys['systvs'] = array();
    foreach($arrbuff as $value)
        $sys['systvs'][] = trim($value);

    // Check real input name if defined in the form     
    if(isset($_GET['inputName']) && !empty($_GET['inputName']))
        $sys['form_input_name'] = htmlspecialchars($_GET['inputName']);
    else if(isset($_POST['inputname']) && !empty($_POST['inputname']))
        $sys['form_input_name'] = htmlspecialchars($_POST['inputname']);
    // Implement real input name for some window template variables
    $tpl['windowcontent']['resp_inputname'] = $sys['form_input_name']; 
    $tpl['windowjsbody']['php_js_block'] = 'var inputName = "'
        .$sys['form_input_name'].'";';

}


function genImgRemove() 
{
    $upload = uploadMain::getInstance();
    $prefFile = $upload->getPropValue(FILTER_PROP, 'prefix_thumb_file_name');
    $tmpDir = $upload->getPropValue(FILTER_PROP, 'tmpdir');
    $uid = $upload->getPropValue(SYSTEM_PROP, 'uid');
    // Search pattern
    $pattern = '{'.$prefFile.$uid.'*.jpg,'.$prefFile.$uid.'*.jpeg,'
        .$prefFile.$uid.'*.png,'.$prefFile.$uid.'*.gif}';

    $prefix = explode('-', $prefFile);
    $prefix[0] .= '-';
   
    foreach(glob($tmpDir.$pattern, GLOB_BRACE|GLOB_ERR) as $imgpath ) {
        $imgpathBig = str_replace($prefix[0], '', $imgpath);
        // Extract file name component of path
        $imagename = basename($imgpath);
        $imagenameBig = basename($imgpathBig);
        if($_GET['delImageId'] == $imagename) {
            @unlink($imgpath);
            @unlink($imgpathBig);
            return true;
        }
    }
    return false;
}
    
// This function displays current user images based on 
// UID (userid_date_standardname.jpg [.png] [.gif])
// The Auto-Clean function deletes automatically old 
// files after a specified elapsed time.
function genMultiFileDisplays() 
{
    $pattern = '';
    $iniParams  = array();
    $upload = uploadMain::getInstance();

    $iniParams = array_merge((array)$upload->getPropValue(FILTER_PROP),
                (array)$upload->getPropValue(TEMPLATE_PROP),
                (array)$upload->getPropValue(FILTER_PROP),
                (array)$upload->getPropValue(SYSTEM_PROP));
    // Generate search pattern
    $pattern = '{'.$iniParams['prefix_thumb_file_name'].'*.jpg,'
                .$iniParams['prefix_thumb_file_name'].'*.jpeg,'
                .$iniParams['prefix_thumb_file_name'].'*.png,'
                .$iniParams['prefix_thumb_file_name'].'*.gif}';

    $js_counter = 0;
    $prefix = explode('-', $iniParams['prefix_thumb_file_name']);
    $prefix[0] .= '-';
    $loop_tpl = '';
    
    foreach(glob($iniParams['tmpdir'].$pattern, GLOB_BRACE|GLOB_ERR) as $imgpath ) {
        $imgpathBig = str_replace($prefix[0], '', $imgpath);
        // extract filename component of path
        $image = basename($imgpath); 
        // extract user id, time & real name components of tmp-image 
        list($id, $datetime, $stdname) = explode('_', $image, 3);
        // delete old images if elapsed time more then 
        // 'hours_before_deleting' value, inside upload.ini.php file
        $d = mktime(date('H')-($iniParams['hours_before_deleting']), date('i'),
                date('s'), date('m'), date('d'), date('Y'));
        // current time
        $now = mktime(date('H'),date('i'),date('s'), date('m'), date('d'), date('Y'));
        // time difference
        $diff = $now - $d;
        if(($datetime + $diff) <= $now) {
            unlink($imgpath);
            unlink($imgpathBig);
            continue;   
        }

        if(false === stripos($imgpath, $iniParams['prefix_thumb_file_name'].$iniParams['uid'].'_'))
            continue;

        $js_counter++;
        // Generate a delete image link
        $cbuff = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES)
                    .'?delImageId='.$image.'&amp;inputName=';
        // Dynamic template variables
        $upload->setPropValue(TEMPLATE_PROP, 'imgname_bg', $imgpathBig, 'windowimageloop'); 
        $upload->setPropValue(TEMPLATE_PROP, 'image',      $image,      'windowimageloop'); 
        $upload->setPropValue(TEMPLATE_PROP, 'stdname',    $stdname,    'windowimageloop');
        $upload->setPropValue(TEMPLATE_PROP, 'js_counter', $js_counter, 'windowimageloop');
        $upload->setPropValue(TEMPLATE_PROP, 'href_delete',$cbuff,      'windowimageloop');

        if(true !== $upload->callParentMethod('registerDoc', 'imagelooptpl', $iniParams[
                    'templatedir'].'/window.image.loop.tpl'))
            return false;
        if(true !== $upload->callParentMethod('parseTV', 'imagelooptpl', 
                    $upload->getPropValue(TEMPLATE_PROP, 'windowimageloop')))
            return false;
        
        $loop_tpl .= $upload->getPropValue(TEMPLATE_PROP, 'imagelooptpl', 'tpls');
    }
    return $upload->setPropValue(TEMPLATE_PROP, 'imagelooptpl', $loop_tpl, 'tpls');
}
    
// Generate some TV's for 
// body-javaScript block
function genJsInitsDisplay() 
{
    $upload = uploadMain::getInstance();
    $jsparam = '';
    $jsparam = $upload->getPropValue(TEMPLATE_PROP, 'js_counter', 'windowimageloop');
    $upload->setPropValue(TEMPLATE_PROP, 'js_counter', $jsparam, 'windowjsbody');
}


function genOutput($inputId = '') 
{
    $tempDir = '';

    $upload = uploadMain::getInstance();

    // If function is repeatedly called so exchange inputname and repeat output action 
    if($upload->getPropValue(TEMPLATE_PROP, 'form_input_name', 'formtemplate') != 
                $inputId && !empty($inputId)) {
        $upload->setPropValue(TEMPLATE_PROP, 'form_input_name', $inputId, 'formtemplate');
        $upload->setPropValue(TEMPLATE_PROP, 'form_button_id',  $inputId, 'formtemplate');  
    }

    $tempDir = $upload->getPropValue(SYSTEM_PROP, 'templatedir');

    if(true !== $upload->callParentMethod('registerDoc', 'forminclude', 
                $upload->getPropValue(SYSTEM_PROP, 'script_root')
                .'/'.$tempDir.'/form.select.include.tpl')) {

        echo genMessage(0);
        exit();
    }
    if(true !== $upload->callParentMethod('parseTV', 'forminclude', 
                $upload->getPropValue(TEMPLATE_PROP, 'formtemplate'))) {

        echo genMessage(0);
        exit();
    }
    echo ($upload->getPropValue(TEMPLATE_PROP, 'forminclude', 'tpls'));
}
// This function formats message 
// string by using of HTML <pre> tags 
function genMessage($opt) {
    $upload = uploadMain::getInstance();
    if($opt == 0)
        return '<pre>

    '.$upload->getPropValue(ERROR_PROP, 'value').'
    -----------------------------------------------------------
    Error level: '.$upload->getPropValue(ERROR_PROP, 'level').'
    </pre>';
}
?>
