/* 
 * Used to load datatables and populate transactions table
 */

jQuery(document).ready(function ()
{
    jQuery('#qp-transactions').DataTable(
            {
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": qp_ajax.transactions_url,
                    "data": function (d)
                    {
                        d.action = "process_transactions"
                    }
                }

            }
    );
  
});


