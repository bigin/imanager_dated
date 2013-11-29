<h3 class="menuglava">[[item-menu-titel]]</h3>
<form class="largeform" action="load.php?id=item_manager" method="post" accept-charset="utf-8">
<input name="id" type="hidden" value="[[item-id]]" />
<div class="manager-wrapper">
    <p class="element-wrapper"><label for="title">[[items/title]]</label>
    <input id="title" class="im-title" name="post-title" type="text" value="[[item-title]]" placeholder="[[items/fill_me]]" /></p>
    <p class="element-wrapper"><label for="category">[[items/category]]</label>
    <select id="category" class="im-text" name="category">
        [[option-tpl]]
    </select></p>
    <p class="element-wrapper"><label for="page">[[items/details_page]]</label>
    <select id="page" class="im-text" name="page">
        [[option-tpl-page]]
    </select></p>
    [[custom-fields]]
    <p class="element-wrapper"><input name="submit" type="submit" class="submit" value="[[items/savebutton]]" />
    <a href="load.php?id=item_manager" class="cancel" title="Cancel">[[items/cancel]]</a></p>
</div>
</form>
			
