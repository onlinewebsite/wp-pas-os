jQuery(document).ready(function($) {
    jQuery("#pas-os-form").on('submit',function() {
        jQuery('.pas-os-result').removeClass('pas-os-success');
        jQuery('.pas-os-result').removeClass('pas-os-error');
        jQuery('.pas-os-result').html('در حال دریافت .... ');
        $.ajax({
            url: ajaxurl,
            dataType : "json",
            data: {
                'action': 'pas_os_ajax_admin_request',
                'pas_os_form' : jQuery(this).serialize(),
            },
            success:function(response) {
                if(response.status){
                    jQuery('.pas-os-result').html(response.content);
                    jQuery('.pas-os-data').html(response.result.data+response.result.chart);
                    pas_os_chart_js();
                } else {
                    jQuery('.pas-os-result').html(response.content);
                }

                //console.log(data);
            },
            error: function(errorThrown){
                jQuery('.pas-os-result').html('خطا در دریافت. مجددا امتحان کنید');
                jQuery('.pas-os-result').addClass('pas-os-error');
                //console.log(errorThrown);
            }
        });
        return false;
    });
    jQuery("#pas-os-client").on('submit',function() {
        jQuery('.pas-os-result').removeClass('pas-os-success');
        jQuery('.pas-os-result').removeClass('pas-os-error');
        jQuery('.pas-os-result').html('در حال دریافت .... ');
        $.ajax({
            url: ajaxurl,
            dataType : "json",
            data: {
                'action': 'pas_os_ajax_client_admin_request',
                'pas_os_form' : jQuery(this).serialize(),
            },
            success:function(response) {
                if(response.status){
                    jQuery('.pas-os-result').html(response.content);
                    jQuery('.pas-os-data').html(response.result.data+response.result.chart);
                    pas_os_chart_js();
                } else {
                    jQuery('.pas-os-result').html(response.content);
                }

                //console.log(data);
            },
            error: function(errorThrown){
                jQuery('.pas-os-result').html('خطا در دریافت. مجددا امتحان کنید');
                jQuery('.pas-os-result').addClass('pas-os-error');
                //console.log(errorThrown);
            }
        });
        return false;
    });
});