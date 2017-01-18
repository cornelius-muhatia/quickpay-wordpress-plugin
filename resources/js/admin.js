/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
      //validate custom amount and multiplier they can't both be selected
    jQuery('#qp_custom_amount').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#qp_multiplier').prop('checked', false);
        }
    });
    jQuery('#qp_multiplier').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#qp_custom_amount').prop('checked', false);
        }
    });
});
