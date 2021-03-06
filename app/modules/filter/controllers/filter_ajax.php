<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Filter_Ajax
 * @package Modules\Filter\Controllers
 *
 * @property Mdl_Clients $mdl_clients
 * @property Layout $layout
 *
 * @TODO Complete overhaul needed
 */
class Filter_Ajax extends User_Controller
{
    public $ajax_controller = true;

    /**
     * Returns a list of filtered invoices based on the given query
     * @uses $_POST['filter_query']
     */
    public function filter_invoices()
    {
        $this->load->model('invoices/mdl_invoices');

        $query = $this->input->post('filter_query');

        $keywords = explode(' ', $query);
        $params = array();

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = strtolower($keyword);
                $this->mdl_invoices->like("CONCAT_WS('^',LOWER(invoice_number),invoice_date_created,invoice_date_due,LOWER(client_name),invoice_total,invoice_balance)",
                    $keyword);
            }
        }

        $data = array(
            'invoices' => $this->mdl_invoices->get()->result(),
            'invoice_statuses' => $this->mdl_invoices->statuses()
        );

        $this->layout->load_view('invoices/partial_invoice_table', $data);
    }

    /**
     * Returns a list of filtered quotes based on the given query
     * @uses $_POST['filter_query']
     */
    public function filter_quotes()
    {
        $this->load->model('quotes/mdl_quotes');

        $query = $this->input->post('filter_query');

        $keywords = explode(' ', $query);
        $params = array();

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = strtolower($keyword);
                $this->mdl_quotes->like("CONCAT_WS('^',LOWER(quote_number),quote_date_created,quote_date_expires,LOWER(client_name),quote_total)",
                    $keyword);
            }
        }

        $data = array(
            'quotes' => $this->mdl_quotes->get()->result(),
            'quote_statuses' => $this->mdl_quotes->statuses()
        );

        $this->layout->load_view('quotes/partial_quote_table', $data);
    }

    /**
     * Returns a list of filtered clients based on the given query
     * @uses $_POST['filter_query']
     */
    public function filter_clients()
    {
        $this->load->model('clients/mdl_clients');

        $query = $this->input->post('filter_query');

        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = strtolower($keyword);
                $this->mdl_clients->like("CONCAT_WS('^',LOWER(name),LOWER(email),phone,is_active)",
                    $keyword);
            }
        }

        $data = array(
            'clients' => $this->mdl_clients->with_total_balance()->get()->result()
        );

        $this->layout->load_view('clients/partial_client_table', $data);
    }

    /**
     * Returns a list of filtered payments based on the given query
     * @uses $_POST['filter_query']
     */
    public function filter_payments()
    {
        $this->load->model('payments/mdl_payments');

        $query = $this->input->post('filter_query');

        $keywords = explode(' ', $query);
        $params = array();

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = strtolower($keyword);
                $this->mdl_payments->like("CONCAT_WS('^',payment_date,LOWER(invoice_number),LOWER(client_name),payment_amount,LOWER(payment_method_name),LOWER(payment_note))",
                    $keyword);
            }
        }

        $data = array(
            'payments' => $this->mdl_payments->get()->result()
        );

        $this->layout->load_view('payments/partial_payment_table', $data);
    }
}
