<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Base_Controller
 * @package Core
 * @property CI_Config $config
 * @property CI_Input $input
 * @property CI_Lang $lang
 * @property Mdl_Settings $mdl_settings
 */
class Base_Controller extends MX_Controller
{
    public $ajax_controller = false;

    /**
     * Base_Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->config->load('invoice_plane');

        // Don't allow non-ajax requests to ajax controllers
        if ($this->ajax_controller and !$this->input->is_ajax_request()) {
            exit;
        }

        $this->load->helper('url');

        // Check if database has been configured, if not show the welcome page
        if (!file_exists(APPPATH . 'config/database.php')) {

            $this->load->helper('redirect');
            redirect('/start');

        } else {
            // Load globally used libraries and helpers
            $this->load->database();

            $this->load->library('form_validation');

            $this->load->helper('alert');
            $this->load->helper('date');
            $this->load->helper('invoice');
            $this->load->helper('ip');
            $this->load->helper('number');
            $this->load->helper('pager');
            $this->load->helper('redirect');
            $this->load->helper('user');

            // Load setting model and load settings
            $this->load->model('settings/mdl_settings');
            $this->mdl_settings->load_settings();

            // Define the theme URL
            if ($this->mdl_settings->setting('theme') != '') {
                define('THEME_URL', base_url() . 'themes/' . $this->mdl_settings->setting('theme') . '/');
            } else {
                define('THEME_URL', base_url() . 'themes/InvoicePlane/');
            }
            
            // Get the app key
            define('APP_KEY', $this->mdl_settings->setting('app_key'));

            // Define encryption key
            $this->config->set_item('encryption_key', $this->mdl_settings->setting('encryption_key'));

            // Load session
            $this->config->set_item('sess_save_path', STORAGE_SESSIONS);
            $this->load->library('session');

            // Load language strings
            $this->lang->load('ip', $this->mdl_settings->setting('default_language'));
            $this->lang->load('form_validation', $this->mdl_settings->setting('default_language'));
            $this->lang->load('custom', $this->mdl_settings->setting('default_language'));
            $this->load->helper('language');

            // Load the layout module
            $this->load->module('layout');

        }
    }

}
