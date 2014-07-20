<tr class="[[tr-class]]">
    <td>
        <input type="text" class="text" style="width:80px;padding:2px;" name="cf_[[i]]_key" value="[[key]]"/>
    </td>
    <td>
        <input type="text" class="text" style="width:140px;padding:2px;" name="cf_[[i]]_label" value="[[label]]"/>
    </td>
    <td>
        <select name="cf_[[i]]_type" class="text short" style="width:180px;padding:2px;" >
            <option value="text" [[selected-text]]>[[imanager/text_field_value]]</option>
            <option value="textfull" [[selected-longtext]]>[[imanager/longtext_field_value]]</option>
            <option value="dropdown" [[selected-dropdown]]>[[imanager/dropdown_field_value]]</option>
            <option value="checkbox" [[selected-checkbox]]>[[imanager/checkbox_field_value]]</option>
            <option value="editor" [[selected-editor]] >[[imanager/editor_field_value]]</option>
            <option value="hidden" [[selected-hidden]]>[[imanager/hidden_field_value]]</option>
            <option value="uploader" [[selected-file]]>[[imanager/file_field_value]]</option> 
        </select>
        <textarea class="text" style="width:170px;height:50px;padding:2px;[[area-display]]" name="cf_[[i]]_options">[[area-options]]</textarea>
    </td>
    <td>
        <input type="text" class="text" style="width:100px;padding:2px;" name="cf_[[i]]_value" value="[[text-options]]"/>
    </td>
    <td class="delete">
        <a href="#" class="delete" title="[[imanager/delete]]">X</a>
    </td>
</tr>
