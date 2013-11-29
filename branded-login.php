<?php
/*
Plugin Name: Branded Login
Description: Upload and display a logo at the login screen
Version: 1.1
Author: Chris Cagle
URL: http://www.cagintranet.com/
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");
define('BRANDEDLOGOPATH', GSDATAUPLOADPATH .'branded-logo/');

# register plugin
register_plugin(
	$thisfile,
	'Branded Login',
	'1.1',
	'Chris Cagle',
	'http://www.cagintranet.com/',
	'Upload and display a logo at the login screen',
	'theme',
	'brandedlogin_form'
);

# hooks
add_action('theme-sidebar','createSideMenu',array($thisfile,'Branded Login'));
add_action('index-login','brandedlogin_logo');
add_action('header','brandedlogin_css');

# functions
function brandedlogin_logo() {
	global $SITENAME;
	global $SITEURL;
	$logoFile=brandedlogin_findfile();
	if ($logoFile) {
		echo '<img src="'. $logoFile .'" alt="'.$SITENAME.'" id="branded-logo" />';
	}
}

function brandedlogin_css() {
	echo '<!-- for Branded Login plugin -->
	<style type="text/css" >
		#index .main {width:270px;}
		#index .main h3 {display:none;}
		#index .main #branded-logo {margin-bottom:20px;}
		#index #sidebar {display:none;}
	</style>
	';	
}

function brandedlogin_form() {
	global $i18n;
	
	if (isset($_FILES["file"])){
		if ($_FILES["file"]["error"] > 0)		{
			$error = $i18n['ERROR_UPLOAD'];
		} else 	{
			$logo_info = pathinfo($_FILES["file"]["name"]);
			$logo_file = BRANDEDLOGOPATH .'logo.'. $logo_info['extension'];
			if (! file_exists(BRANDEDLOGOPATH)) {
				mkdir(BRANDEDLOGOPATH);
			}	
			move_uploaded_file($_FILES["file"]["tmp_name"], $logo_file);

			$success = $i18n['FILE_SUCCESS_MSG'];
		}
	}
	
	?>
	<h3>Branded Login</h3>

	<form action="<?php	echo $_SERVER ['REQUEST_URI']?>" method="post" enctype="multipart/form-data">
		<p><input type="file" class="text" name="file" id="file" /></p>
		<p><input type="submit" class="submit" name="submit" value="<?php echo $i18n['UPLOAD']; ?>" /> &nbsp; <span class="hint">Under 230px wide is best</span></p>
	</form>
	
	
	<h3>Current Photo</h3>
	<?php
	
	echo '<p><img src="'. brandedlogin_findfile() .'" alt="" /></p>';
	
}

function brandedlogin_findfile() {
	global $SITEURL;
	
	if (file_exists(BRANDEDLOGOPATH)) {
		$filenames = getFiles(BRANDEDLOGOPATH);
		foreach ($filenames as $file) {
			if ($file == "." || $file == ".." || is_dir(BRANDEDLOGOPATH . $file) || $file == ".htaccess" ) {
				// not a upload file
			} else {
				return $SITEURL.'data/uploads/branded-logo/' .$file;
			}
		}
	} else {
		# no folder or files yet
		return false;
	}

}
