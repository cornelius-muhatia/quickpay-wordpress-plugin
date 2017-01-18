<?php

defined('ABSPATH') or die('No script kiddies please!');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Used to create and perform database operations for quickpay transactions
 *
 * @author User
 */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Qpg_Model
{

    //declare variables    
    private $table_name;
    private $wpdb;
    private $primary_key = "id";
    private $trans_id = "transaction_id";
    private $auth_id = "authentication_id";
    private $merchant_id = "merchant_id";
    private $ref_no = "reference_no";
    private $token = "request_token";
    private $receipt_no = "receipt_no";
    private $currency = "currency";
    private $amount = "amount";
    private $order_info = "order_info";
    private $response_code = "response_code";
    private $created_at = "created_at";
    private $description = "description";
    private $charset_collate;

    function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . "quickpay_checkout";
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Used to create default tables that will be used by the plugin e.g. transaction table
     */
    function create_schema()
    {
        $sql = "
      CREATE TABLE IF NOT EXISTS " . $this->table_name . "("
                . $this->primary_key . " BIGINT PRIMARY KEY AUTO_INCREMENT, "
                . $this->response_code . " TINYINT, "
                . $this->trans_id . " TEXT, "
                . $this->auth_id . " TEXT, "
                . $this->merchant_id . " TEXT, "
                . $this->ref_no . " TEXT, "
                . $this->token . " TEXT, "
                . $this->receipt_no . " TEXT, "
                . $this->currency . " VARCHAR(10), "
                . $this->amount . " VARCHAR(200), "
                . $this->order_info . " TEXT, "
                . $this->description . " TEXT, "
                . $this->created_at . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                . ")" . $this->charset_collate;
        dbDelta($sql);
    }

    /**
     * Used to insert a new transaction
     * @param type $data
     */
    function insert_transaction($data)
    {

        $sql = "INSERT INTO " . $this->table_name . "(" . $this->amount . ", " . $this->auth_id . ", " . $this->currency . ", "
                . $this->order_info . ", " . $this->trans_id . ", " . $this->token . ", " . $this->response_code . ","
                . " " . $this->ref_no . "," . $this->receipt_no . ", " . $this->merchant_id . ", " . $this->description . ")"
                . "values(%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
        $sql = $this->wpdb->prepare($sql, $data['amount'], $data['auth_id'], $data['currency'], $data['order_info'], $data['trans_id'], $data['token'], $data['response_code'], $data['ref_no'], $data['receipt_no'], $data['merchant_id'], $data['description']);
        $this->wpdb->query($sql);
    }

    /**
     * Used to get transactions using datatables specifications
     * @return type
     */
    function get_transactions()
    {
        $aColumns = array($this->primary_key,
            $this->response_code, $this->trans_id, $this->auth_id, $this->merchant_id,
            $this->ref_no, $this->token, $this->receipt_no, $this->order_info,
            $this->description, $this->amount, $this->created_at, $this->currency);




        //$input = & $_GET;


        $sLimit = "";
//        if (isset($input['iDisplayStart']) && $input['iDisplayLength'] != '-1')
//        {
//            $sLimit = " LIMIT " . intval($input['iDisplayStart']) . ", " . intval($input['iDisplayLength']);
//        }
        if (filter_has_var(INPUT_GET, 'start') && filter_input(INPUT_GET, 'length') != '-1')
        {
            $sLimit = " LIMIT " . intval(filter_input(INPUT_GET, 'start')) . ", " . intval(filter_input(INPUT_GET, 'length'));
        }


        /**
         * Ordering
         */
        $aOrderingRules = array();
//        if (isset($input['iSortCol_0']))
//        {
//            $iSortingCols = intval($input['iSortingCols']);
//            for ($i = 0; $i < $iSortingCols; $i++)
//            {
//                if ($input['bSortable_' . intval($input['iSortCol_' . $i])] == 'true')
//                {
//                    $aOrderingRules[] = "`" . $aColumns[intval($input['iSortCol_' . $i])] . "` "
//                            . ($input['sSortDir_' . $i] === 'asc' ? 'asc' : 'desc');
//                }
//            }
//        }else 
        if (filter_has_var(INPUT_GET, 'order'))
        {
            $order = filter_input(INPUT_GET, 'order', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $aOrderingRules[] = "'" . $aColumns[intval($order[0]['column'])] . "' " . $order[0]['dir'];
        }

        if (!empty($aOrderingRules))
        {
            //$sOrder = " ORDER BY " . implode(", ", $aOrderingRules);
            $sOrder = " ORDER BY " . $aColumns[intval($order[0]['column'])] . " " . $order[0]['dir'];
        } else
        {
            $sOrder = "";
        }


        /**
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $iColumnCount = count($aColumns);


        $search = filter_input(INPUT_GET, 'search', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (isset($search['value']))
        {
            $aFilteringRules = array();

            for ($i = 0; $i < $iColumnCount; $i++)
            {

                $column = filter_input(INPUT_GET, 'columns', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                if (isset($column[$i]['searchable']) && $column[$i]['searchable'] == 'true')
                {
                    $aFilteringRules[] = "" . $aColumns[$i] . " LIKE '%" . $this->wpdb->_real_escape($search['value']) . "%' ";
                }
            }
            if (!empty($aFilteringRules))
            {
                $aFilteringRules = ' WHERE '. implode(" OR ", $aFilteringRules) . '';
            }
        }

        /**
         * SQL queries
         * Get data to display
         */
        $aQueryColumns = array();
        foreach ($aColumns as $col)
        {
            if ($col != ' ')
            {
                $aQueryColumns[] = $col;
            }
        }

        $sQuery = "
    SELECT SQL_CALC_FOUND_ROWS `" . implode("`, `", $aQueryColumns) . "`
    FROM `" . $this->table_name . "`" .$aFilteringRules. ' '. $sOrder . $sLimit;

        $rResult = $this->wpdb->get_results($sQuery, ARRAY_A); //$db->query($sQuery) or die($db->error);
// Data set length after filtering
        $sQuery = "SELECT FOUND_ROWS()";
        $rResultFilterTotal = $this->wpdb->get_results($sQuery, ARRAY_N);
         //$db->query($sQuery) or die($db->error);
        list($iFilteredTotal) = $rResultFilterTotal[0]; //$rResultFilterTotal->fetch_row();
// Total data set length
        $sQuery = "SELECT COUNT(`" . $this->primary_key . "`) FROM `" . $this->table_name . "`";
        $rResultTotal = $this->wpdb->get_results($sQuery, ARRAY_N); //$this->wpdb->query($sQuery);//$db->query($sQuery) or die($db->error);
        list($iTotal) = $rResultTotal[0]; //$rResultTotal->fetch_row();


        /**
         * Output
         */
        $output = array(
            "draw" => intval(filter_input(INPUT_GET, 'draw')),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

//        while ($aRow = $rResult->fetch_assoc())
        foreach ($rResult as $aRow)
        {
            $row = array();
            for ($i = 0; $i < $iColumnCount; $i++)
            {
                if ($aColumns[$i] == $this->amount)
                {
                    // Special output formatting for 'version' column
                    $row[] = $aRow[$aColumns[$iColumnCount - 1]] . ' ' . $aRow[$aColumns[$i]]; //($aRow[$aColumns[$i]] == '0') ? '-' : $aRow[$aColumns[$i]];
                } elseif ($aColumns[$i] != ' ')
                {
                    // General output
                    $row[] = $aRow[$aColumns[$i]];
                }
            }
            $output['aaData'][] = $row;
        }

        //echo json_encode($output);
        return $output;
    }

}
