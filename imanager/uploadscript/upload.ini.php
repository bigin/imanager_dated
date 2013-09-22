<?php
// Global properties
$par_systeminfo = array(
    // Script inf
    'script_name'       => 'ImageUploader',
    'script_version'    => '1.5',
    'script_created_by' => 'Mongo',
    'script_date'       => 'Juni 2011',
    // This is the script root directory
    'script_root'       => dirname(__FILE__),
    // The language files directory name (default: lang)
    'languagedir'       => 'lang',
    // Enter your language here 
    // default: english. (Check your 'lang' directory for included language files)
    'language'          => 'english',
    // The template directory name (default: tpl)
    'templatedir'       => 'tpl',
    // Classes directory name (default: model)
    'classesdir'        => 'model',
    /* This property contains a relative path to the temp. directory,
       which is used as a part for to generating of 'src'-attribut values.
       Be sure that the final slash is included */
    'tmpurl'            => 'tmp/',
    // Default inputname if “genOutput()“ called without argument.
    // This value can be determined by calling the “genOutput('myFile')“ function with argumet
    'form_input_name'   => 'myfile',
    // Define HTML-attributes to autoremove those key when element values are empty.
    'systvs'            => 'class, id, name, content, xml:lang, lang',
    // Session authentification. For activate this option enter 'On'.
    'sess_auth'         => '',
);


// Template placeholders
$par_template = array(

    // Include form template
    'formtemplate' => array(

        // Info part comment
        'info_comment'             => $par_systeminfo['script_name'].
                                     ' Version: '.$par_systeminfo['script_version'].
                                     ' Created by '.$par_systeminfo['script_created_by'].
                                     ' '.$par_systeminfo['script_date'],      
        'upload_script_url'        => str_replace($_SERVER['DOCUMENT_ROOT'].'/', 
                          '', $par_systeminfo['script_root'].'/uploadwindow.php'),
        // Window width & height
        'win_w'                    => 800,
        'win_h'                    => 600,
        // Window positions
        'win_left_pos'             => 100,
        'win_top_pos'              => 100,
        // Text input css class and id
        'form_input_class'         => '',
        'form_input_id'            => '[[element-id]]',
        // Button name
        'form_button_name'         => '',
        // Button CSS-class & id
        'form_button_class'        => '', 
        'form_button_id'           => '',
        
    ),

    // Window header template
    'windowheader' => array(

        'html_element_lang'        => 'en',
        'html_element_xml_lang'    => 'en',
        // Enter path to your css style resources 
        'include_css'              => $par_systeminfo['templatedir'].'/styles.css',
        // The title for your window panel
        'form_title'               => 'MY UPLOAD FORM'


    ),
	
    // Window content template
    'windowcontent'  => array(
        
        'form_action'              => htmlspecialchars($_SERVER['PHP_SELF']),
        // CSS-class & id for the div container and form elements like inputs, etc.
        'form_cont_elements_class' => 'inputcont',
        'form_cont_elements_id'    => '',
        // You can set override css classe and id for your image container
        'multi_data_cont_class'    => 'multicont',
        'multi_data_cont_id'       => '',
        // Name of the input type file
        'input_name_file'          => 'inputnam',
        // Max file size of the form input (note: its unsafe can be easily manipulated), 
        // instead use 'maxsize_mainimg' parameter below in the filter section
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

        // Window title
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
    // Max. width for thumbnail
    'max_thumb_width'        => 140,
    // Limited number of images in tmp folder for each user ID
    'images_total_count'     => 10,
    // Allowed MIME-type (Currently only gif, jpg and png formats are supported)
    'allowed_files'          => 'image/gif, image/jpeg, image/png',
    // Search pattern and file extension for your file names.([[+uid]]-TV inclusive) 
    'files_pattern'          => '{*[[+uid]]*.gif, *[[+uid]]*.jpg, *[[+uid]]*.jpeg, *[[+uid]]*.png}',
    // Allowed character for the name of file
    'filename_reg_exp'       => '/^[a-z_]([a-z0-9_-]*[a-z0-9-])*\.[a-z]{3,4}$/i',
    /* This is the image directory path that should appear in the form input after selecting the image. 
       This value can be a relative or an absolute path  Sample: 
       '/var/www/hosts/myhostdir/httpdocs/upload/tmp/'
       If you're not sure what your absolute path is, check the phpinfo() DOCUMENT_ROOT directive. 
       NOTE: 'tmpdir' parameter value you have to enter a slash '/' as the final character. */
    'tmpdir'                 => 'tmp/',
    // File name prefix, Sample: usruserip_filename_datatime.jpg
    'prefix_file_name'       => 'usr',
    // Thumbinal name prefix looks like this: thumb-usruserip_filename_datatime.jpg 
    'prefix_thumb_file_name' => 'thumb-usr',
    // Name of the input type file
    'input_name_file'        => $par_template['windowcontent']['input_name_file'],
    // the tmp-File will be deleted automatically after a certain period of time 
    // you can enter 1, 2, 3, 4, ... (hour format) as value
    'hours_before_deleting'  => 2,
);
