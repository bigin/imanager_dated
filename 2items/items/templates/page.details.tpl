<div class="im-itemwrap" >
    <script type="text/javascript">
    (function($) {
        var cache = [];
        // Arguments are image paths relative to the current page.
        $.preLoadImages = function() {
        var args_len = arguments.length;
        for (var i = args_len; i--;) {
            var cacheImage = document.createElement("img");
            cacheImage.src = arguments[i];
            cache.push(cacheImage);
        }
    }
    })(jQuery)

    jQuery.preLoadImages("[[image-1]]", "[[image-2]]", "[[image-3]]", "[[image-4]]", "[[image-5]]", "[[image-6]]");

    jQuery(document).ready(function ($) {
        $("a.im-linkpic").click(function () {
            var mytemp = jQuery($(this)).attr("id");
            $("#im-screen-img").fadeOut("slow", function() {
                jQuery("#im-screen-img").attr("src", mytemp );
                $("#im-screen-img").fadeIn();
            });
             return false;// "cancel" the default behavior of following the link
        });
    });
    </script>
    <img id="im-screen-img" width="500" alt="" class="im-pic" src="[[image-1]]" />
    <h2 id="im-itemtitle">[[title]]</h2>
    <div id="im-rightcol">
        <p class="im-descrtext">[[description]]</p>
        <form id="im-shopform" action="#" method="post">
            <input type="submit" name="send" class="im-sendbutton" value="ZUM SHOP" />
        </form>
        <div id="im-cont">
            [[loop-tpl]]
        </div>
    </div>
</div>
