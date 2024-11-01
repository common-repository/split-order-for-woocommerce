jQuery(document).ready(function () {
    jQuery("#attribute").on('change', function () {
        var id = jQuery(this).val();  
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {id: id, action: 'wos_select_variation'},
            beforeSend: function () {
                //jQuery(".loading-box").show();
            },
            success: function (data) {
                console.log(data);
                jQuery('#variation').html(data);
            },
            error: function (xhr) { // if error occured
                //jQuery(".loading-box").hide();
            },
        });
    });
});