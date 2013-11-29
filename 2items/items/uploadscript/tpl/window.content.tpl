    <form action="[[+form_action]]" method="post" enctype="multipart/form-data" >
	<div class="[[+form_cont_elements_class]]" id="[[+form_cont_elements_id]]">
	    <input type="hidden" name="MAX_FILE_SIZE" value="[[+html_maxfilesize]]" />
	    <input type="file" name="[[+input_name_file]]" maxlength="[[+html_imgfilelength]]" />
	    <input type="submit" name="submit" value="[[+submit_text]]" />
	    <input type="hidden" name="inputname" value="[[+resp_inputname]]" />
	</div>
	<div class="[[+multi_data_cont_class]]" id="[[+multi_data_cont_id]]">
	    <p class="messagecont">[[+lang_info_display]]<br /><span>[[+lang_select_image]]</span></p>
[[+imagelooptpl]]
[[+jsbodyblock]]
	</div>
    </form>