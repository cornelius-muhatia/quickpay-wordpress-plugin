<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!defined('WP_UNINSTALL_PLUGIN'))
{
    exit(1);
}
//clean plugin data sorry we can't delete transaction data due to PCI Compliance requirements
delete_option('quickpay_checkout_vars');

