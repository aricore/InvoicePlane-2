<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Mdl_Payment_Custom
 * @package Modules\Custom_Fields\Models
 */
class Mdl_Payment_Custom extends MY_Model
{
    public $table = 'ip_payment_custom';
    public $primary_key = 'ip_payment_custom.payment_custom_id';

    /**
     * Saves a custom field for payments to the database
     * @param $payment_id
     * @param $db_array
     */
    public function save_custom($payment_id, $db_array)
    {
        $payment_custom_id = null;

        $db_array['payment_id'] = $payment_id;

        $payment_custom = $this->where('payment_id', $payment_id)->get();

        if ($payment_custom->num_rows()) {
            $payment_custom_id = $payment_custom->row()->payment_custom_id;
        }

        parent::save($payment_custom_id, $db_array);
    }
}
