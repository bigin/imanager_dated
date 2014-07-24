<?php
// Global properties
$par_systeminfo = array(
    // Script inf
    'script_name'       => 'ImageUploader',
    'script_version'    => '1.5',
    'script_created_by' => 'Mongo',
    'script_date'       => 'Juni 2011',
    // Path to scripts root directory
    'script_root'       => dirname(__FILE__),
    // The language files directory name (default: lang)
    'languagedir'       => 'lang',
    /* Enter your language here. Default: english. 
    (Check your 'lang' directory for language files) */
    'language'          => 'english',
    // Template directory name (Default: tpl)
    'templatedir'       => 'tpl',
    // Classes directory name (default: model)
    'classesdir'        => 'model',
    /*  This property contains a relative path to the temp directory,
    which is used to build the 'src'-attribut values. Please verify that  
    the final slash is included */
    'tmpurl'            => 'tmp/',
    /* Default inputname if “genOutput()“ is called without argument. This
    value can be overwritten by calling the “genOutput('myCustomField')“ 
    function with an argumet */
    'form_input_name'   => 'myfile',
    /* Define HTML-attributes to autoremove those from template if 
    contents of these attributes is empty. */
    'systvs'            => 'class, id, name, content, xml:lang, lang',
    // Session authentification, enter 'On' to enable this feature.
    'sess_auth'         => '',
);


// Template placeholders
$par_template = array(
    // Form template
    'formtemplate' => array(
        // Info comment part
        'info_comment'             => $par_systeminfo['script_name'].
                                     ' Version: '.$par_systeminfo['script_version'].
                                     ' Created by '.$par_systeminfo['script_created_by'].
                                     ' '.$par_systeminfo['script_date'],      
        'upload_script_url'        => str_replace($_SERVER['DOCUMENT_ROOT'].'/', 
                          '', $par_systeminfo['script_root'].'/uploadwindow.php'),
        // Window width & height (Upload interface menu)
        'win_w'                    => 800,
        'win_h'                    => 600,
        // Window positions
        'win_left_pos'             => 100,
        'win_top_pos'              => 100,
        // css class and id of the text input element
        'form_input_class'         => '',
        'form_input_id'            => '[[element-id]]',
        // Button name
        'form_button_name'         => '',
        // CSS-class & id of the button
        'form_button_class'        => '', 
        'form_button_id'           => '',
        
    ),

    // Upload interface header template
    'windowheader' => array(
        'html_element_lang'        => 'en',
        'html_element_xml_lang'    => 'en',
        // path to your CSS file 
        'include_css'              => $par_systeminfo['templatedir'].'/styles.css',
        // title for your upload interface
        'form_title'               => 'MY UPLOAD FORM'


    ),
	
    // Upload interface content template
    'windowcontent'  => array(
        
        'form_action'              => htmlspecialchars($_SERVER['PHP_SELF']),
        // CSS-class & id for the div container and form elements like inputs, etc.
        'form_cont_elements_class' => 'inputcont',
        'form_cont_elements_id'    => '',
        // There you can set css classe and id for your image container
        'multi_data_cont_class'    => 'multicont',
        'multi_data_cont_id'       => '',
        // Name of the input type file
        'input_name_file'          => 'inputnam',
        /* Max file size of the form input (unsafe), you cam use instead 'maxsize_mainimg' 
        parameter below in the filter section */
        'html_maxfilesize'         => '',
        'lang_info_display'        => '',
        'lang_select_image'        => 'Click an “Image” to include in your article.',

    ),

    'windowimageloop' => array(

        // css classes and ids
        'imgcont_css_class'     => 'imgcont',
        'imgcont_id'            => '',
        'imglink_css_class'     => 'imglink',
        'imglink_id'            => '',
        'link_css_class'        => 'selectlink',
        'link_id'               => '',
        'link_delete_css_class' => 'deletelink',
        'link_delete_id'        => '',
        'link_x_class'          => 'deletexlink',
        'link_x_id'             => '',
        'tmpurl_image'          => $par_systeminfo['tmpurl'],

    ),

    'windowbody' => array(
        // Upload interface title
        'headline'              => 'Upload your images here',
    ),
 );


// Image filter parameters
$par_imagefilter = array(
    // Max. characters per name
    'filename_length'        => 50,
    // Min. width in pixels
    'minwidth_mainimg'       => 0,
    // Max width in pixels
    'maxwidth_mainimg'       => 6000,
    // Min. height in pixels
    'minheight_mainimg'      => 0,
    // Max. height in pixels
    'maxheight_mainimg'      => 6000,
    // Maximum allowed image size in Bytes (1Mb value expl: 1048576)
    'maxsize_mainimg'        => 20971520,
    // Max. thumbnail width
    'max_thumb_width'        => 140,
    // Limited number of images in tmp folder for each user ID
    'images_total_count'     => 10,
    // Allowed MIME-type (Currently only gif, jpg and png formats supported)
    'allowed_files'          => 'image/gif, image/jpeg, image/png',
    // Search pattern and file extension for your file names.([[+uid]]-TV inclusive) 
    'files_pattern'          => '{*[[+uid]]*.gif, *[[+uid]]*.jpg, *[[+uid]]*.jpeg, *[[+uid]]*.png}',
    // Allowed character in the filename
    'filename_reg_exp'       => '/^[a-z_]([a-z0-9_-]*[a-z0-9-])*\.[a-z]{3,4}$/i',
    /* image directory path. It can be a relative or an absolute path, sample: 
    '/var/www/hosts/myhostdir/httpdocs/upload/tmp/'
    If you're not sure what your absolute path is, check the phpinfo() DOCUMENT_ROOT directive. 
    NOTE: you have to enter the slash '/' as the final character. */
    'tmpdir'                 => 'tmp/',
    // File name prefix, sample: usruserip_filename_datatime.jpg
    'prefix_file_name'       => 'usr',
    // Thumbinal name prefix looks like this: thumb-usruserip_filename_datatime.jpg 
    'prefix_thumb_file_name' => 'thumb-usr',
    // Name of the input type file
    'input_name_file'        => $par_template['windowcontent']['input_name_file'],
    // the tmp-File will deleted automatically after a certain period of time 
    // you can enter 1, 2, 3, 4, ... (hour format)
    'hours_before_deleting'  => 2,
);
