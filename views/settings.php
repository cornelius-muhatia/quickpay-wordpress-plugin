<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 *This file has dependency of CustomArrayAccess.php which should be included before loading this file
 */
$hidden_field_name = 'quickpay_settings_form';

// Read in existing option value from database
$vars = new Qpg_CustomArrayAccess(maybe_unserialize(get_option('quickpay_checkout_vars')), '');
// See if the user has posted us some information
// If they did, this hidden field will be set to 'Y'
if (filter_has_var(INPUT_POST, $hidden_field_name) && filter_input(INPUT_POST, $hidden_field_name) == 'Y')
{
    //handle file upload
    if (isset($_FILES['qp_icon']))
    {
        $uploaded = media_handle_upload('qp_icon', 0);
        // Error checking using WP functions
        if (is_wp_error($uploaded))
        {
           // echo "Error uploading file: " . $uploaded->get_error_message();
        } else
        {
            $vars['qp_icon'] = $uploaded;
          //  echo "File upload successful!";
        }
    }
    $vars['qp_environment'] = filter_input(INPUT_POST, 'qp_environment');
    $vars['qp_merchant'] = filter_input(INPUT_POST, 'qp_merchant');
    $vars['qp_merchant_desc'] = filter_input(INPUT_POST, 'qp_merchant_desc');
    $vars['qp_button'] = filter_input(INPUT_POST, 'qp_button');
    $vars['qp_public_key'] = filter_input(INPUT_POST, 'qp_public_key');
    $vars['qp_private_key'] = filter_input(INPUT_POST, 'qp_private_key');
    $vars['qp_custom_amount'] = filter_input(INPUT_POST, 'qp_custom_amount');
    $vars['qp_multiplier'] = filter_input(INPUT_POST, 'qp_multiplier');
    $vars['qp_currency'] = filter_input(INPUT_POST, 'qp_currency');
    // Save the posted value in the database
    update_option('quickpay_checkout_vars', maybe_serialize($vars));



    // Put a "settings saved" message on the screen
    ?>
    <div class="updated"><p><strong><?php _e('settings saved.', 'menu-test'); ?></strong></p></div>
    <?php
}
// settings form
?>
<div class="wrap">
    <!-- Title -->
    <?php echo "<h2>" . __('Quickpay Checkout', 'qp-payment-gateway') . "</h2> "; ?>
    <form class="form-table" name="form1" method="post" enctype="multipart/form-data" action="">-    
        <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Environment", "qp-payment-gateway"); ?></label> <br>
            <label class="radio-inline">
                <input type="radio" name="qp_environment" <?php if ($vars['qp_environment'] == 0)
{
    echo 'checked="checked"';
} ?> value="0" /><?php _e("TEST", "qp-payment-gateway"); ?>                         </label>
            <label class="radio-inline">
                <input type="radio" name="qp_environment" <?php if ($vars['qp_environment'] == 1)
{
    echo 'checked="checked"';
} ?> value="1" /><?php _e("LIVE", "qp-payment-gateway"); ?>                         </label>
            <span id="helpBlock2" class="help-block"><?php _e("Switch between test and live environment", "qp-payment-gateway"); ?></span>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Merchant Name", "qp-payment-gateway"); ?></label>
            <input type="text" value="<?php echo $vars['qp_merchant'] ?>" required="" class="form-control" minlength="1" maxlength="30"  name="qp_merchant" aria-describedby="helpBlock2">
            <span class="help-block"><?php _e("Merchant name registered with Quickpay", "qp-payment-gateway"); ?></span>
        </div>
        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Merchant Description", "qp-payment-gateway"); ?></label>
            <input type="text" value="<?php echo $vars['qp_merchant_desc'] ?>" required="" class="form-control" minlength="1" maxlength="30"  name="qp_merchant_desc" aria-describedby="helpBlock2">
            <span class="help-block"><?php _e("Brief Merchant Description", "qp-payment-gateway"); ?></span>
        </div>
        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Payment Button", "qp-payment-gateway"); ?></label>
            <input type="text" value="<?php echo $vars['qp_button']; ?>" required="" class="form-control" minlength="1" maxlength="30" name="qp_button" aria-describedby="helpBlock2">
            <span class="help-block"><?php _e("Payment Button Text", "qp-payment-gateway"); ?></span>
        </div>    
        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Icon", "qp-payment-gateway"); ?></label>
            <input type="file" name="qp_icon" aria-describedby="icon-help" />
            <span id="icon-help" class="help-block"><?php _e("Icon to be displayed on Payment Dialog preferably your logo. (Expected dimensions 150 * 150)", "quickpay-checkout"); ?></span>
        </div>
        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Public Key", "qp-payment-gateway"); ?></label>
            <input type="text" value="<?php echo $vars['qp_public_key'] ?>" required="" class="form-control" minlength="1" maxlength="30" name="qp_public_key" aria-describedby="helpBlock2">
            <span class="help-block"><?php _e("Public Key issued by Quickpay", "qp-payment-gateway"); ?></span>
        </div>
        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Private Key", "qp-payment-gateway"); ?></label>
            <input type="text" value="<?php echo $vars['qp_private_key'] ?>" required="" class="form-control" minlength="1" maxlength="30" name="qp_private_key" aria-describedby="helpBlock2">
            <span class="help-block"><?php _e("Private Key issued by Quickpay", "qp-payment-gateway"); ?></span>
        </div>
        <div class="form-group">
            <label class="control-label" for="inputSuccess1"><?php _e("Currency", "qp-payment-gateway"); ?></label>
            <select type="text" value="<?php echo $vars['qp_currency'] ?>" required="" class="form-control" name="qp_currency">
                <option value="USD">US Dollars</option>
                <option value="KES" <?php if($vars['qp_currency'] == 'KES'){ echo 'selected="selected"'; } ?>>Kenya Shilling</option>
            </select>
            <span class="help-block"><?php _e("Currency to be used", "qp-payment-gateway"); ?></span>
        </div>
        <div class="form-group">
            <label class="radio-inline">
                <input type="checkbox" <?php if ($vars['qp_custom_amount'] == 1)
{
    echo "checked='checked'";
} ?> name="qp_custom_amount" id="qp_custom_amount" value="1">   <?php _e("Custom Amount", "qp-payment-gateway"); ?>                      </label>

            <span id="helpBlock2" class="help-block"><?php _e("User to determine amount", "qp-payment-gateway"); ?></span>
        </div>
        <div class="form-group">
            <label class="radio-inline">
                <input type="checkbox" <?php if ($vars['qp_multiplier'] == 1)
{
    echo "checked='checked'";
} ?> name="qp_multiplier" id="qp_multiplier" value="1">   <?php _e("Multiplier", "qp-payment-gateway"); ?>                    </label>

            <span id="helpBlock2" class="help-block"><?php _e("Payments for more than one item", "qp-payment-gateway"); ?></span>
        </div>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
        </p>

    </form>
</div>


