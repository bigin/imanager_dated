<?php
class filter 
{
    private $upload = array();
    private $iniParams = array();
    private $lang = array();
    private $imginfo = array();
    private $imageCount = 0;
    private $atime = '';
 
    function __construct() 
    {
        $this->upload = uploadMain::getInstance();
    
        $this->lang = $this->upload->getPropValue(LANGUAGE_PROP);
        $this->iniParams = array_merge((array)$this->upload->getPropValue(FILTER_PROP),
                                  (array)$this->upload->getPropValue(SYSTEM_PROP));
    
    }
    
    public function fileFilter() 
    {
        // Error buffer
        $err = '';
        // Buffer
        $tbuf = $this->iniParams['input_name_file'];

        // Check status constantes 
        switch ($_FILES[$tbuf]['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_inisize']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
            case UPLOAD_ERR_FORM_SIZE:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_formsize']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_partial']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_nofile']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_notmpdir']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
            case UPLOAD_ERR_CANT_WRITE:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_cantwrite']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
            case UPLOAD_ERR_EXTENSION:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_extension']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
            case UPLOAD_ERR_OK:
                $this->imginfo = @getimagesize($_FILES[$tbuf]['tmp_name']);
                $allowed = array();
                $allowed = explode(',', $this->iniParams['allowed_files']);

                for($i = 0; $i < count($allowed); $i++)
                    $allowed[$i] = trim($allowed[$i]);
                if(!$this->imginfo || !isset($this->imginfo['mime']) || 
                            !in_array($this->imginfo['mime'], $allowed)) {
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_format']);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(true !== $this->dimensionFilter()) {
                    $err = sprintf($this->lang['err_imgfile_dim'], 
                                $this->iniParams['minwidth_mainimg'],
                    $this->iniParams['minheight_mainimg'],
                    $this->iniParams['maxwidth_mainimg'],
                    $this->iniParams['maxheight_mainimg']);
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $err);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(true !== $this->sizeFilter($_FILES[$tbuf]['tmp_name'])) {
                    $err = sprintf($this->lang['err_imgfile_siz'], 
                                $this->iniParams['maxsize_mainimg']);
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $err);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(true !== $this->charFilter($_FILES[$tbuf]['name'])) {
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_character']);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(true !== $this->lengthFilter($_FILES[$tbuf]['name'])) {
                    $err = sprintf($this->lang['err_namelength'], 
                    $this->iniParams['filename_length']);
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $err);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(true !== $this->duplicateFilter($_FILES[$tbuf]['name'])) {
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_file_exists']);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(true !== $this->totalNumberFilter()) {
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_images_total']);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(true !== $this->changeFileDir()) {
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_image_move']);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                if(-1 === $this->generateThumbinail()) {
                    $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                    $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_image_thumb']);
                    $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                    return false;
                }
                return true;
            default:
                $this->upload->setPropValue(ERROR_PROP, 'indicator', true);
                $this->upload->setPropValue(ERROR_PROP, 'value', $this->lang['err_unknow']);
                $this->upload->setPropValue(ERROR_PROP, 'level', 3);
                return false;
        }
    }
    
    // Check width & height
    private function dimensionFilter() 
    {
        $w = round($this->imginfo["0"]);
        $h = round($this->imginfo["1"]);
        if($w < $this->iniParams['minwidth_mainimg'] && 
                    $this->iniParams['minwidth_mainimg'] != '')
            return false;
        else if($w > $this->iniParams['maxwidth_mainimg'] 
                    && $this->iniParams['maxwidth_mainimg'] != '')
            return false;
        else if($h < $this->iniParams['minheight_mainimg'] 
                    && $this->iniParams['minheight_mainimg'] != '')
            return false;
        else if($h > $this->iniParams['maxheight_mainimg'] 
                    && $this->iniParams['maxheight_mainimg'] != '')
            return false;
        return true;
    }
    
    // Ceck file size in bytes
    private function sizeFilter($upfile) 
    {
        // filesize() -Gives file size in bytes
        $size = filesize($upfile);
        if($size > $this->iniParams['maxsize_mainimg'] 
                    && $this->iniParams['maxsize_mainimg'] != '')
            return false;
        return true;
    }

    // Invalid character check
    private function charFilter($upfile) 
    {
        if(!preg_match($this->iniParams['filename_reg_exp'], $upfile))
            return false;
        return true;
    }
    
    // Check filename length
    private function lengthFilter($upfile) 
    {
        if(strlen($upfile) >= $this->iniParams['filename_length'])
            return false;
        return true;
    }
    
    // Check duplicate image name
    private function duplicateFilter($upfile) 
    {
        $uid = $this->iniParams['uid'];

        if(!empty($this->iniParams['files_pattern'])) {
            $patt = $this->iniParams['tmpdir'].str_replace(' ', '', 
                        str_replace('[[+uid]]', $uid, $this->iniParams['files_pattern']));
        } else {
            // Default
            $patt = $this->iniParams['tmpdir'].'{*'.$uid.'*.gif,*'
                        .$uid.'*.jpg,*'.$uid.'*.jpeg,*'.$uid.'*.png}';
        }
        foreach(glob($patt, GLOB_BRACE|GLOB_ERR) as $imgname ) {
            $image = basename($imgname);
            list($id, $datetime, $stdname) = explode('_', $image, 3);
            $this->imageCount++;
            $dl_dataname = ($this->iniParams['prefix_file_name']
                        .$this->iniParams['uid'].'_'.$upfile);

            if($id.'_'.$stdname == $dl_dataname)
                return false;
        }
        $this->imageCount = ($this->imageCount / 2);
        return true;
    }

    // Check maximum number of images
    private function totalNumberFilter() 
    {
        if($this->imageCount >= $this->iniParams['images_total_count'])
            return false;
        return true;
    }
    


    // Move image to our tmp directory and rename it: uid_datatime_dateiname.*
    private function changeFileDir() 
    {
        $this->atime = mktime(date('H'),date('i'),date('s'), date('m'), date('d'), date('Y'));
        $oldDir = $_FILES[$this->iniParams['input_name_file']]['tmp_name'];
        $newDir = $this->iniParams['tmpdir'].$this->iniParams['prefix_file_name']
                    .$this->iniParams['uid'].'_'.$this->atime.'_'.basename(
                    $_FILES[$this->iniParams['input_name_file']]['name']);
                
        return (move_uploaded_file($oldDir, $newDir));
    }
    
    // The Thumbnail-Generator. 
    // The Generator shrinks thumbnail size to fit size given 
    // in the ini file parameter. This can happen only if width 
    // of the image is over than 'max_thumb_width' value. 
    // Otherwise, thumbnail receives the same size as the 
    // original image. 
    private function generateThumbinail() 
    {
        $w = $this->imginfo[0];
        $h = $this->imginfo[1];
        $prUsr = $this->iniParams['prefix_file_name'];
        $prTbUsr = $this->iniParams['prefix_thumb_file_name'];
        $uid = $this->iniParams['uid'];
        // Shorten standard image path
        $stdImg  = $this->iniParams['tmpdir'].$this->iniParams['prefix_file_name'].$uid.'_'.
                    $this->atime.'_'.basename($_FILES[$this->iniParams['input_name_file']]['name']);
        // Shorten thumbnail path
        $thumbImg = $this->iniParams['tmpdir'].$this->iniParams['prefix_thumb_file_name'].$uid.'_'
                    .$this->atime.'_'.basename($_FILES[$this->iniParams['input_name_file']]['name']);
      
        if($this->iniParams['max_thumb_width'] >= $w && !empty($w)) {
            $nw = $w;
            $nh = $h;
        } else if(empty($w)) {
            unlink($stdImg);
            return -1;
        } else {
            $nw = $this->iniParams['max_thumb_width']; 
            $nh = intval( $h * $nw / $w);
        }
        switch ($this->imginfo[2]) {
            case 1: 
                // Gif 
                $handlImage = ImageCreateFromGIF($stdImg);
                if (!$handlImage) {
                    $handlImage = ImageCreate (150, 30);
                    $bgc = ImageColorAllocate ($handlImage, 255, 255, 255);
                    $tc  = ImageColorAllocate ($handlImage, 0, 0, 0);
                    ImageFilledRectangle ($handlImage, 0, 0, 150, 30, $bgc); 
                    /* error */
                    $err = sprintf($this->lang['err_opening'], $stdImg);
                    ImageString($handlImage, 1, 5, 5, $err, $tc);
                    ImageDestroy($handlImage);

                    unlink($stdImg);
                    return -1; 
                }
                $newThumb = ImageCreate($nw, $nh); 
                ImageCopyResized($newThumb, $handlImage, 0,0,0,0, $nw, $nh, $w, $h); 
                ImageGIF($newThumb, $thumbImg, 100);
                ImageDestroy($handlImage);
                break;
            case 2:
                // Jpg
                $handlImage = '';
                $handlImage = ImageCreateFromJPEG($stdImg);
                if (!$handlImage) {
                    $handlImage = ImageCreate (150, 30);
                    $bgc = ImageColorAllocate ($handlImage, 255, 255, 255);
                    $tc  = ImageColorAllocate ($handlImage, 0, 0, 0);
                    ImageFilledRectangle ($handlImage, 0, 0, 150, 30, $bgc); 
                    /* error */
                    $err = sprintf($this->lang['err_opening'], $stdImg);
                    ImageString($handlImage, 1, 5, 5, $err, $tc);
                    ImageDestroy($handlImage);

                    unlink($stdImg);
                    return -1; 
                }
                $newThumb = imagecreatetruecolor($nw, $nh); 
                ImageCopyResized($newThumb, $handlImage, 0,0,0,0, $nw, $nh, $w, $h); 
                ImageJPEG($newThumb, $thumbImg, 100);
                ImageDestroy($handlImage);
                break;
            case 3: 
                // Png
                $handlImage = '';
                $handlImage = ImageCreateFromPNG ($stdImg);
                if (!$handlImage) {
                    $handlImage = ImageCreate (150, 30); 
                    $bgc = ImageColorAllocate ($handlImage, 255, 255, 255);
                    $tc  = ImageColorAllocate ($handlImage, 0, 0, 0);
                    ImageFilledRectangle ($handlImage, 0, 0, 150, 30, $bgc); 
                    /* error */
                    $err = sprintf($this->lang['err_opening'], $stdImg);
                    ImageString($handlImage, 1, 5, 5, $err, $tc);
                    ImageDestroy($handlImage);

                    unlink($stdImg);
                    return -1; 
                }
                $newThumb = imagecreatetruecolor($nw, $nh);
                ImageCopyResampled($newThumb, $handlImage, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagepng($newThumb, $thumbImg);
                ImageDestroy($handlImage);
                break;
            default:
                unlink($stdImg);
                return -1;
        }
        return 1;
    }
}
?>
