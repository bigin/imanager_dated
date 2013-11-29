	    <script type="text/javascript">
	    /* <![CDATA[ */
	    var inputName;
	    var counter = [[+js_counter]];
	    [[+php_js_block]]
	    for(i = 0; i < document.links.length; ++i) {
		if(document.links[i].name != '') {
		    document.links[i].href = document.links[i]+inputName;
		}
	    }
	    var x = document.getElementsByName("inputname")[0].value = inputName;
	    /* ]]> */
	    </script>