<h3 class="menuglava">[[items/items-settings]]</h3>
<form class="largeform" action="load.php?id=item_manager&settings&settings_edit" method="post" accept-charset="utf-8">
    <div class="manager-wrapper">
	    <p><label for="manager-title">[[items/choose_manager_title]]</label>
	    <input id="manager-title" type="text" class="text" name="item-title" value="[[manager-title ]]" /></p>
        <h4>[[items/frontend_settings]]</h4>
		<p><label for="category-filter">[[items/filter_by_category]]</label>
	    <input id="category-filter" type="checkbox" class="text" name="filter_by_id" [[category-checked]] /></p>
        <p><label for="sorted-fields">[[items/sorted_by_customfield]]</label>
        <select id="sorted-fields" class="im-text" name="sortby">
            [[option-tpl-page]]
        </select></p>
        <p><label for="items-per-page">[[items/items_per_page]]</label>
	    <input id="items-per-page" type="text" class="text" name="itemsperpage" value="[[items-per-page]]" /></p>
        <h4>[[items/backend_settings]]</h4>
        <!--<p><label for="b-category-filter">Filter by Category</label>
	    <input id="b-category-filter" type="checkbox" class="text" name="b_filter_by_cat" [[bcategory-checked]] /></p>-->
        <p><label for="b-sorted-fields">[[items/sorted_by_customfield]]</label>
        <select id="b-sorted-fields" class="im-text" name="bsortby">
            [[b-option-tpl-page]]
        </select></p>
        <p><label for="bitems-per-page">[[items/items_per_page]]</label>
	    <input id="bitems-per-page" type="text" class="text" name="bitemsperpage" value="[[bitems-per-page]]" /></p>
		<p><label for="thumb-w">[[items/max_thumb_size]]</label>
	    <input id="thumb-w" type="text" class="text" name="thumbwidth" value="[[thumb-w]]" /></p>
        <p><span><input class="submit" type="submit" name="settings_edit" value="[[items/submit_settings]]" /></span></p>
    </div>
</form>
