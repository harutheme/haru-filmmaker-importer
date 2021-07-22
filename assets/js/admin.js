(function($) {

    "use strict";

    $(document).ready(function() {

        $('body').on('click', '.meks-notice .notice-dismiss', function(){

            $.ajax( {
                url: ajaxurl,
                method: "POST",
                data: {
                    action: 'meks_remove_notification'
                }
            });

        });


    });

})(jQuery);