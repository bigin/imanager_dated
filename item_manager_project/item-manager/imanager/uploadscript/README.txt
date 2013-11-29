
   UPLOADSCRIPT

   Version 0.5 Beta by Mongo, Juni 2011
   E-Mail: juri.ehret@gmail.com

   This program is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free Software
   Foundation; either version 2 of the License, or (at your option) any later
   version.
  
   This program is distributed in the hope that it will be useful, but WITHOUT
   ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
   FOR A PARTICULAR PURPOSE. See the GNU General Public License for more 
   details.

   You should have received a copy of the GNU General Public License along with 
   this program; if not, write to the Free Software Foundation, Inc., 59 Temple
   Place, Suite 330, Boston, MA 02111-1307 USA



   This is the text file which includes information about the script and shows 
   you a simple way how to use Uploader in your web projects. Moreover, this 
   makes it much easier to understand how script functions and the possible 
   field of application.

   The Uploadscript was primarily developed to be applied for online interactive 
   forms that consisting of several pages and allows a multiple integration of 
   the images. This Uploadscript is independent of your web application, for im-
   plementing the script does not require modifications made to your web resour-
   ces. The only thing you need to do is adding following lines: 
   `<?php include('upload.php'); ?>` and `<?php genOutput(); ?>` as content in 
   your resource and ensure the PHP has access to write for the `tmp` folder in
   the root of the script directory. For more detailed information on the imple-
   mentation flow, have a look at the suitable documentation section below.
  


   What do you need to do for implement the Uploadscript in your web projects?
   Minimum requirements:

      1. A webspace with PHP5 support.
         Warning: Perhaps the script does not work with earlier PHP version.

      2. GD-Library for PHP. The GD is an open source code library for the dyna-
         mic creation of images by programmers. GD is written in C, and 
         `wrappers` are available for Perl, PHP and other languages. If you are 
         unsure if you have GD library, you can run phpinfo() to check that GD 
         Support is enabled. If you don't have it, you can download it for free:
         http://www.boutell.com/gd/ 



   It's really simple to set up the Uploadscript to be used on private indivi-
   dualservers or personal webspaces.
   The easiest way to use this script quickly and easily:
   
      1. First of, download and unpack Uploadscript packet on your webspace.

      2. Then you have to define access rights. It must be possible to give PHP- 
         process write permissions for `tmp` folder in the root directory of
         the upload script.
  
      3. Browse to the script folder and open a file named -`upload.ini.php` 
         for editing. There you'll see some important parameter values that you 
         may need to change or complete. Some of them include paths, CSS-classes 
         or numeric values like file size, width, height etc. Please check 
         correctness of the data and if necessary enter your own system specific 
         values here. But please change this values only, if you know exactly 
         what you are doing.

      4. Okay, the next step open your form or other PHP-resource where you want 
         to integrate the Uploadscript and add following lines as content:

            <?php 
               include('your_path_to_scriptdirectory/upload.php');
               genOutput(); 
            ?>

         Replace `your_path_to_scriptdirectory` with your own path to `script
         root` directory.
 
         If you're planning to allow multiple files upload you have to specify
         input names when calling `genOutput` function, sample:

            <?php 
               include('your_path_to_scriptdirectory/upload.php');
               genOutput('myInput1');
               genOutput('myInput2');
               genOutput('myInput3');
            ?>
   
        One more tip: For our example program implementation, I've setup an PHP-
        file that name is `index.php`, this file is located in the program dire-
        ctory.



   Directory tree, file information and what function does that have.
   A quick look into directory of the script:

      `upload.ini.php`    -The application configuration file. Admi-
                           nistrators can change or modify internal 
                           program settings here. 
                           WARNING: Be careful when changing the se-
                                    curity and filter settings.

      `upload.php`        -It's a controller of the form view and 
                           simultaneously a Program entry point.

      `uploadwindow.php`  -The window (upload panel) controller. 

      `README.txt`        -The thing you are reading now.

      `index.php`         -Here is a simple example of how to inte-
                           grate the script within an simple form.
      

       
      `tmp` directory:

         The `tmp` directory is a kind of buffer and contains all uploaded imag-
         es and generated thumbnails. The old registration images will be dele-
         ted automatically after some time according to your specific settings 
         in the `upload.ini.php` file. After the successful upload the images 
         files into directory, they can be considered for further processing by 
         your own application.

         NOTE: You must make sure during further processing that these files are 
               really from the `tmp` directory.

         A `.htaccess` file are also located in that directory. This file is not 
         strictly necessary for use of the script, but will improve additional 
         safety by using the apache -`ForceType`
         (http://httpd.apache.org/docs/2.0/mod/core.html#forcetype) 
         and -`Header`
         (http://httpd.apache.org/docs/2.0/mod/mod_headers.html#header)
         directives.

         NOTE: I should like to point out that one needs -`mod_headers` to use 
               `Header` set. Btw, an `.htaccess` file could only be used by set-
               ting the `AllowOverride` directive to `All` in apache configura-
               tions file. 


      `lang` directory:
      
         The `lang` directory contains language files, currently only English 
         and German languages are available. You can exchange values of these 
         files or translate them into your native language and change the lan-
         guage settings within your `upload.ini.php` file to.

    
      `tpl` directory: 
      
         The `tpl` directory contains all HTML-templates and CSS files. You can
         use them to change the look of the user download panel.


      `model` directory:

         The `model` directory contains core and entire class library of the 
         Uploadscript.

         `upload.class.php`          -This basis interface basically provides a 
                                      series of methods and attributes that are 
                                      used as a framework for the implementation 
                                      of individual elements in various program 
                                      sections. 

         `document.parser.class.php` -A page rendering class. These are the ba-
                                      sic building methods to render templates.

         `image.filter.class.php`    -A standard Image control is wrapped within
                                      a class includes and validity check for 
                                      uploaded images. The class also contains 
                                      method to generate thumbnails by the use 
                                      of an GD-Library

         `generator.php`             -Contains a collection of specific tempora-
                                      ry help and output procedures.    



   Well, for more detailed information how to operate the Uploadscrip you'll 
   need to look at the script core.
