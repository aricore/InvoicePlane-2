<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Mdl_Custom_Fields
 * @package Modules\Custom_Fields\Models
 */
class Mdl_Custom_Fields extends MY_Model
{
    public $table = 'ip_custom_fields';
    public $primary_key = 'ip_custom_fields.custom_field_id';

    /**
     * The default select directive used in every query
     */
    public function default_select()
    {
        $this->db->select('SQL_CALC_FOUND_ROWS *', false);
    }

    /**
     * Returns the table names that are used to store the custom fields
     * @return array
     */
    public function custom_tables()
    {
        return array(
            'ip_client_custom' => 'client',
            'ip_invoice_custom' => 'invoice',
            'ip_payment_custom' => 'payment',
            'ip_quote_custom' => 'quote',
            'ip_user_custom' => 'user'
        );
    }

    /**
     * Returns all available field types
     * @return array
     */
    public function custom_types()
    {
        return array(
            'ip_fieldtype_input' => 'input_field',
            'ip_fieldtype_textarea' => 'textarea_field'
        );
    }

    /**
     * Returns the validation rules for custom fields
     * @return array
     */
    public function validation_rules()
    {
        return array(
            'custom_field_table' => array(
                'field' => 'custom_field_table',
                'label' => lang('table'),
                'rules' => 'required'
            ),
            'custom_field_type' => array(
                'field' => 'custom_field_type',
                'label' => lang('type'),
                'rules' => 'required'
            ),
            'custom_field_label' => array(
                'field' => 'custom_field_label',
                'label' => lang('label'),
                'rules' => 'required|max_length[50]'
            )
        );
    }

    /**
     * Returns the prepared database array
     * @return array
     */
    public function db_array()
    {
        // Get the default db array
        $db_array = parent::db_array();

        // Get the array of custom types
        $custom_types = $this->custom_types();

        // Get the array of custom tables
        $custom_tables = $this->custom_tables();

        // Check if the user wants to add 'id' as custom field
        if (strtolower($db_array['custom_field_label']) == 'id') {
            // Replace 'id' with 'field_id' to avoid problems with the primary key
            $custom_field_label = 'field_id';
        } else {
            $custom_field_label = strtolower(str_replace(' ', '_', $db_array['custom_field_label']));
        }

        // Create the name for the custom field column

        $this->load->helper('diacritics');

        $clean_name = preg_replace('/[^a-z0-9_\s]/', '', strtolower(diacritics_remove_diacritics($custom_field_label)));

        $db_array['custom_field_column'] = $custom_tables[$db_array['custom_field_table']] . '_custom_' . $clean_name;

        // Return the db array
        return $db_array;
    }

    /**
     * Overrides the basic save function to allow custom functions
     * @param null $id
     * @param null $db_array
     * @return null
     */
    public function save($id = null, $db_array = null)
    {
        if ($id) {
            // Get the original record before saving
            $original_record = $this->get_by_id($id);
        }

        // Create the record
        $db_array = ($db_array) ? $db_array : $this->db_array();

        // Save the record to ip_custom_fields
        $id = parent::save($id, $db_array);

        if (isset($original_record)) {
            if ($original_record->custom_field_column <> $db_array['custom_field_column']) {
                // The column name differs from the original - rename it
                $this->rename_column($db_array['custom_field_table'], $original_record->custom_field_column,
                    $db_array['custom_field_column']);
            }
        } else {
            // This is a new column - add it
            $this->add_column($db_array['custom_field_table'], $db_array['custom_field_column']);
        }

        return $id;
    }

    /**
     * Adds a new column to a custom field table
     * @param $table_name
     * @param $column_name
     */
    private function add_column($table_name, $column_name)
    {
        $this->load->dbforge();

        $column = array(
            $column_name => array(
                'type' => 'TEXT'
            )
        );

        $this->dbforge->add_column($table_name, $column);
    }

    /**
     * Renameds a column in a custom field table
     * @param $table_name
     * @param $old_column_name
     * @param $new_column_name
     */
    private function rename_column($table_name, $old_column_name, $new_column_name)
    {
        $this->load->dbforge();

        $column = array(
            $old_column_name => array(
                'name' => $new_column_name,
                'type' => 'TEXT'
            )
        );

        $this->dbforge->modify_column($table_name, $column);
    }

    /**
     * Deletes the custom field form the database
     * @param $id
     */
    public function delete($id)
    {
        $custom_field = $this->get_by_id($id);

        if ($this->db->field_exists($custom_field->custom_field_column, $custom_field->custom_field_table)) {
            $this->load->dbforge();
            $this->dbforge->drop_column($custom_field->custom_field_table, $custom_field->custom_field_column);
        }

        parent::delete($id);
    }

    /**
     * Query filter used to specify the working table
     * @param $table
     * @return $this
     */
    public function by_table($table)
    {
        $this->filter_where('custom_field_table', $table);
        return $this;
    }

}
