<h3 class="menuglava">[[item-menu-titel]]</h3>
<form class="largeform" action="load.php?id=imanager" method="post" accept-charset="utf-8">
<input name="id" type="hidden" value="[[item-id]]" />
<input name="edititem" type="hidden" value="[[item-edit]]" />
<input name="page" type="hidden" value="[[back-page]]" />
<div class="manager-wrapper">
	<p class="element-wrapper"><label for="category">[[imanager/category_exchange]]</label>
    <select id="category" class="im-text" name="post-category">
        [[option-tpl]]
    </select><input name="reloader" class="im-cat-reload" title="[[imanager/relad_page]]" type="submit" value="" /></p>
    <p class="element-wrapper"><label for="title">[[imanager/title]]</label>
    <input id="title" class="im-title" name="post-title" type="text" value="[[item-title]]" placeholder="[[imanager/fill_me]]" /></p>
    <!--<p class="element-wrapper"><label for="page">[[imanager/details_page]]</label>
    <select id="page" class="im-text" name="post-page">
        [[option-tpl-page]]
    </select></p>-->
    [[custom-fields]]
    <p class="element-wrapper"><input name="submit" type="submit" class="submit" value="[[imanager/savebutton]]" /></p>
</div>
</form>
			
