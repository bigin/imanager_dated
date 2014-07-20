<form id="renameFieldsForm" action="[[formaction]]" method="post">
    <div class="element-wrapper">
        <h4>Rename Tool</h4>
        <p><label for="page-url">[[imanager/searchfor]]</label>
            <input type="text" name="oldname" /></p>
        <p><label for="page-url">[[imanager/switchto]]</label>
            <input type="text" name="newname" /></p>
            <input type="hidden" name="cat" value="[[cat]]" />
        <p><input type="submit" name="sender" value="[[imanager/switchname]]" /></p>
    </div>
</form>
