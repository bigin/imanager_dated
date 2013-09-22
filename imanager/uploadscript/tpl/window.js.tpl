<script type="text/javascript">
/* <![CDATA[ */
var ipath;
function GetImagePath(ipath) {
	var inputName = document.getElementsByName("inputname")[0].value;
	window.opener.document.getElementsByName(inputName)[0].value = ipath;
	window.close();
}
/* ]]> */
</script>