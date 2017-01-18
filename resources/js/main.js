/**var spinner = new Spinner({
 lines: 12, // The number of lines to draw
 length: 7, // The length of each line
 width: 5, // The line thickness
 radius: 10, // The radius of the inner circle
 color: '#000', // #rbg or #rrggbb
 speed: 1, // Rounds per second
 trail: 100, // Afterglow percentage
 shadow: true // Whether to render a shadow
 }).spin(document.getElementById("qp-merchant-form")); */
function processQuickpay()
{
    var spinner = new Spinner().spin(document.getElementById("qp-merchant-form"));
    jQuery('.qp-server-response').html('<div class="isa_info"><i class="fa fa-spinner fa-spin"></i> Processing...</div>');
    var data = jQuery("#qp-merchant-form").serializeArray();
    //alert("Well everything is working okey");
    jQuery
            .ajax({
                type: "POST",
                url: qp_ajax.ajax_url,
                data: data,
                //data: {qpToken: qpToken, amount: amount, currency: currency},
                error: function (res)
                {
                    var msg = '';
                    if (res.status === 500)
                    {
                        msg = (res.responseJSON.message) ? res.responseJSON.message : "Sorry an internal server error occured";
                    }
                    else if (res.status === 403)
                    {
                        msg = "Sorry operation denied";
                    }
                    else if (res.status === 400)
                    {
                        msg = (res.responseJSON.message) ? res.responseJSON.message : "Bad request";
                    }
                    else if (res.status === 0)
                    {
                        msg = "Sorry server cannot be reached. Please check your internet connection";
                    }
                    else
                    {
                        msg = "Sorry unknown error occured (Response code: " + res.status + ")";
                    }
                    console.log('Error: ' + JSON.stringify(res));
                    jQuery('.qp-server-response').html('<div class="isa_error"><i class="fa fa-times-circle"></i>' + msg + '</div>');
                    spinner.stop();

                },
                success: function (res)
                {
                    if (res)
                    {
                        if(res.message){
                            jQuery('.qp-server-response').html('<div class="isa_success"><i class="fa fa-check"></i>' + res.message + '</div>');
                        }
                        spinner.stop();
                    }
                }

            });
};
