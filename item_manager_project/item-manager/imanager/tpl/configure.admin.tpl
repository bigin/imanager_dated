<h4>[[imanager/customfields_title]]</h4>
<p class="clear">[[imanager/customfields_descr]]</p>
<p>[[imanager/customfields_usage]]</p>
<form method="post" id="customfieldsForm">
    <table id="editfields" class="edittable highlight">
        <thead>
            <tr>
                <th>[[imanager/customfields_name]]</th>
                <th>[[imanager/customfields_label]]</th>
                <th style="width:100px;">[[imanager/customfields_type]]</th>
                <th>[[imanager/customfields_default]]</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            [[categorie_items]]    
            <tr>
                <td colspan="4">
                    <a href="#" class="add">[[imanager/customfields_create]]</a>
                </td>
                <td class="secondarylink">
                    <a href="#" class="add" title="[[imanager/customfields_add]]">+</a>
                </td>
            </tr>
        </tbody>
    </table>
    <input type="hidden" name="cat" value="[[cat]]" />
    <input type="submit" name="save" value="[[imanager/customfields_catsender]]" class="submit"/>
</form>
<script type="text/javascript" src="../plugins/imanager/js/jquery-ui.sort.min.js"></script>
<script type="text/javascript">
    function renumberCustomFields() {
        $('#customfieldsForm table tbody tr').each(function(i,tr) {
            $(tr).find('input, select, textarea').each(function(k,elem) {
                var name = $(elem).attr('name').replace(/_\d+_/, '_'+(i)+'_');
                $(elem).attr('name', name);
            });
        });
    }
    $(function() {
        $('select[name$=_type]').change(function(e) {
            var val = $(e.target).val();
            var $ta = $(e.target).closest('td').find('textarea');
            if(val == 'dropdown') 
                $ta.css('display','inline'); 
            else 
                $ta.css('display','none');
        });
        $('a.delete').click(function(e) {
            $(e.target).closest('tr').remove();
            renumberCustomFields();
        });
        $('a.add').click(function(e) {
            var $tr = $(e.target).closest('tbody').find('tr.hidden');
            $tr.before($tr.clone(true).removeClass('hidden').addClass('sortable'));
            renumberCustomFields();
        });
        $('#customfieldsForm tbody').sortable({
            items:"tr.sortable", handle:'td',
            update:function(e,ui) { 
                renumberCustomFields(); 
            }
        });
        renumberCustomFields();
        [[js_element]]
    });
</script>
