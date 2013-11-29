<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#cat").change(function() {
		jQuery.ajax("[[baseurl]]admin/load.php?id=imanager&cat=" 
			+ encodeURIComponent(jQuery(this).val()), {
	    	type: "GET",
	    	contentType: "application/json; charset=utf-8",
	    	success: function(data, status, xhr) {
	        	//javascript: console.log(data);
	        	jQuery(".highlight").html(data);
	    	},
	    	error: function(jqxhr, textStatus, errorThrown) {
	        	javascript: console.log(errorThrown);
	    	},
	    	cache: false
		});
	});
});
</script>