<?php include('upload.php'); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8" ?>';?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Example</title>
<style type="text/css">
    body {font-family:"Helvetica Neue",Helvetica,FreeSans,Arial,Verdana,Geneva,sans-serif;font-size:small;}
    h1 {margin:30px 0 30px;}
    fieldset {margin-bottom:40px;font-weight:bold;border:solid 1px #D6CCC5;}
    label {display:block;cursor:pointer;width:250px;font-weight:normal;margin-top:10px;}
    input, textarea {border:solid 1px #D6CCC5;}
    input {padding:3px;margin-right:3px;}
</style>
<!-- Optionally other elements in the head section -->
</head>
<body>
<h1>Example Uploadform</h1>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <fieldset>
	<legend>Product 1</legend>
	<p ><label for="prodOneName">Label</label>
	    <input type="text" id="prodOneName" name="labelOne" />
	    <label for="myImage1">Image</label><?php genOutput('myImage1'); ?></p>
	<p><label for="proOneText">Description</label>
	    <textarea rows="4" cols="44" id="proOneText"></textarea></p> 
    </fieldset>
    <fieldset>
	<legend>Product 2</legend>
	<p><label for="prodSecName">Label</label>
	    <input type="text" id="prodSecName" name="labelSec" />
	    <label for="myImage2">Image</label><?php genOutput('myImage2'); ?></p>
	<p><label for="proSecText">Description</label>
	    <textarea rows="4" cols="44" id="proSecText"></textarea></p>
    </fieldset>
</form>
</body>
</html>
