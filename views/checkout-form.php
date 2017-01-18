<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Used to create checkout form (appending values search merchant_key, merchant_name)
 * @return String html string of the checkout form
 */
function createForm($options)
{
    $content = <<<HTML
  <form action="javascript:processQuickpay()" data-url="{$options['action_url']}" method="POST" id="qp-merchant-form">            
            <script src="{$options['url']}" 
                    req-key="{$options['qp_public_key']}" 
                    req-store="{$options['qp_merchant']}" 
                    req-desc="{$options['qp_merchant_desc']}" 
                    req-multiplier="{$options['amt_multiplier']}" 
                    req-image="{$options['img_url']}" 
                    {$options['amt_option']}
                    req-currency="{$options['qp_currency']}" 
                    req-button="{$options['qp_button']}"
                    type="text/javascript">
            </script>
                    <input type="hidden" name="action" value="quickpay_checkout" />
                    <div class="qp-server-response"></div>
        </form>
HTML;
    return $content;
}
