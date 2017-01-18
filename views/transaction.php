<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/* 
 *Display all transactions using datatables
 */

?>
<div class="wrap">
     <!-- Title -->
    <?php echo "<h2>" . __('Quickpay Transactions', 'qp-payment-gateway') . "</h2> "; ?>
     
     <!-- Transactions Table -->
     <table id="qp-transactions" class="table table-bordered">
         <thead>
             <tr>
                 <th>Id</th>
                 <th>Response Code</th>
                 <th>Transaction Id</th>
                 <th>Authentication Id</th>
                 <th>Merchant Id</th>
                 <th>Reference No.</th>
                 <th>Request Token</th>
                 <th>Receipt No.</th>
                 <th>Order Information</th>
                 <th>Description</th>
                 <th>Amount</th>
                 <th>Date</th>
             </tr>
         </thead>
         <tbody>
             
         </tbody>
     </table>
</div>