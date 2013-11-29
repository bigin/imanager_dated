<!-- center element on the screen -->
<script type="text/javascript">
jQuery(document).ready(function() {
    var viewportWidth = jQuery(window).width(),
        viewportHeight = jQuery(window).height(),
        $foo = jQuery('#[[item-id]]'),
        elWidth = $foo.width(),
        elHeight = $foo.height(),
        elOffset = $foo.offset();
    jQuery(window)
        .scrollTop(elOffset.top + (elHeight/2) - (viewportHeight/2))
        .scrollLeft(elOffset.left + (elWidth/2) - (viewportWidth/2));
});
</script>
<h3 class="menuglava" >[[items/all_apper]]</h3>
<div class="highlight">
[[content]]
</div>
<div id="im-info-row" >
    <p>[[pagination]]</p>
    <p><strong>[[count]]</strong><span>[[itemmanager-title]]</span></p>
</div>
