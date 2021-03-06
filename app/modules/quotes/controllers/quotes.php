<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Quotes
 * @package Modules\Quotes\Controllers
 * @property CI_DB_query_builder $db
 * @property CI_Loader $load
 * @property Layout $layout
 * @property Mdl_Custom_Fields $mdl_custom_fields
 * @property Mdl_Quotes $mdl_quotes
 * @property Mdl_Quote_Amounts mdl_quote_amounts
 * @property Mdl_Quote_Custom mdl_quote_custom
 * @property Mdl_Quote_Item_Amounts $mdl_quote_item_amounts
 * @property Mdl_Quote_Items mdl_quote_items
 * @property Mdl_Quote_Tax_Rates mdl_quote_tax_rates
 * @property Mdl_Tax_Rates $mdl_tax_rates
 */
class Quotes extends Admin_Controller
{
    /**
     * Quotes constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_quotes');
    }

    /**
     * Index page, redirects to quotes/status/all
     */
    public function index()
    {
        // Display all quotes by default
        redirect('quotes/status/all');
    }

    /**
     * Returns all quotes based on the given status and page no.
     * Example: 'quotes/status/viewed' returns all viewed quotes
     * @param string $status
     * @param int $page
     */
    public function status($status = 'all', $page = 0)
    {
        // Determine which group of quotes to load
        switch ($status) {
            case 'draft':
                $this->mdl_quotes->is_draft();
                break;
            case 'sent':
                $this->mdl_quotes->is_sent();
                break;
            case 'viewed':
                $this->mdl_quotes->is_viewed();
                break;
            case 'approved':
                $this->mdl_quotes->is_approved();
                break;
            case 'rejected':
                $this->mdl_quotes->is_rejected();
                break;
            case 'canceled':
                $this->mdl_quotes->is_canceled();
                break;
        }

        $this->mdl_quotes->paginate(site_url('quotes/status/' . $status), $page);
        $quotes = $this->mdl_quotes->result();

        $this->layout->set(
            array(
                'quotes' => $quotes,
                'status' => $status,
                'filter_display' => true,
                'filter_placeholder' => lang('filter_quotes'),
                'filter_method' => 'filter_quotes',
                'quote_statuses' => $this->mdl_quotes->statuses()
            )
        );

        $this->layout->buffer('content', 'quotes/index');
        $this->layout->render();
    }

    /**
     * Returns the details page for a quote for the given ID
     * @param $quote_id
     */
    public function view($quote_id)
    {
        $this->load->model('mdl_quote_items');
        $this->load->model('tax_rates/mdl_tax_rates');
        $this->load->model('mdl_quote_tax_rates');
        $this->load->model('custom_fields/mdl_custom_fields');
        $this->load->model('custom_fields/mdl_quote_custom');
        $this->load->library('encrypt');

        $quote_custom = $this->mdl_quote_custom->where('quote_id', $quote_id)->get();

        if ($quote_custom->num_rows()) {
            $quote_custom = $quote_custom->row();

            unset($quote_custom->quote_id, $quote_custom->quote_custom_id);

            foreach ($quote_custom as $key => $val) {
                $this->mdl_quotes->set_form_value('custom[' . $key . ']', $val);
            }
        }

        $quote = $this->mdl_quotes->get_by_id($quote_id);


        if (!$quote) {
            show_404();
        }

        $this->layout->set(
            array(
                'quote' => $quote,
                'items' => $this->mdl_quote_items->where('quote_id', $quote_id)->get()->result(),
                'quote_id' => $quote_id,
                'tax_rates' => $this->mdl_tax_rates->get()->result(),
                'quote_tax_rates' => $this->mdl_quote_tax_rates->where('quote_id', $quote_id)->get()->result(),
                'custom_fields' => $this->mdl_custom_fields->by_table('custom_quote')->get()->result(),
                'custom_js_vars' => array(
                    'currency_symbol' => $this->mdl_settings->setting('currency_symbol'),
                    'currency_symbol_placement' => $this->mdl_settings->setting('currency_symbol_placement'),
                    'decimal_point' => $this->mdl_settings->setting('decimal_point')
                ),
                'quote_statuses' => $this->mdl_quotes->statuses()
            )
        );

        $this->layout->buffer(
            array(
                array('modal_delete_quote', 'quotes/modal_delete_quote'),
                array('modal_add_quote_tax', 'quotes/modal_add_quote_tax'),
                array('content', 'quotes/view')
            )
        );

        $this->layout->render();
    }

    /**
     * Deletes a quote from the database
     * @param $quote_id
     */
    public function delete($quote_id)
    {
        // Delete the quote
        $this->mdl_quotes->delete($quote_id);

        // Redirect to quote index
        redirect('quotes/index');
    }

    /**
     * Deletes an item from q given quote
     * @param $quote_id
     * @param $item_id
     */
    public function delete_item($quote_id, $item_id)
    {
        // Delete quote item
        $this->load->model('mdl_quote_items');
        $this->mdl_quote_items->delete($item_id);

        // Redirect to quote view
        redirect('quotes/view/' . $quote_id);
    }

    /**
     * Generate the quote PDF
     * @param $quote_id
     * @param bool $stream
     * @param null $quote_template
     */
    public function generate_pdf($quote_id, $stream = true, $quote_template = null)
    {
        $this->load->helper('pdf');

        if ($this->mdl_settings->setting('mark_quotes_sent_pdf') == 1) {
            $this->mdl_quotes->mark_sent($quote_id);
        }

        generate_quote_pdf($quote_id, $stream, $quote_template);
    }

    /**
     * Removes the quote tax from a quote
     * @param $quote_id
     * @param $quote_tax_rate_id
     */
    public function delete_quote_tax($quote_id, $quote_tax_rate_id)
    {
        $this->load->model('mdl_quote_tax_rates');
        $this->mdl_quote_tax_rates->delete($quote_tax_rate_id);

        $this->load->model('mdl_quote_amounts');
        $this->mdl_quote_amounts->calculate($quote_id);

        redirect('quotes/view/' . $quote_id);
    }

    /**
     * Recalculates the amounts for all quotes
     */
    public function recalculate_all_quotes()
    {
        $this->db->select('quote_id');
        $quote_ids = $this->db->get('quotes')->result();

        $this->load->model('mdl_quote_amounts');

        foreach ($quote_ids as $quote_id) {
            $this->mdl_quote_amounts->calculate($quote_id->quote_id);
        }
    }
}
