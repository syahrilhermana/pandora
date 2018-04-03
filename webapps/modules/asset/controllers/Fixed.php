<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fixed extends CI_Controller
{
    protected $module;
    public function __construct()
    {
        parent::__construct();

        // init twiggy
        $this->twiggy->title('Asset | Fixed');
        $this->module = 'asset/fixed/';
    }

    public function _remap($method, $args=array())
    {
        if($method === 'index') {
            $this->index();
        } else {
            if($this->input->server('REQUEST_METHOD') === 'POST')
                $args = $this->input->post(NULL, TRUE);

            $this->index($method, $args);
        }
    }

    public function index($action=false, $args=array())
    {
        if($action) {
            switch ($action) {
                case 'record'  :
                    $this->get_record_index();
                    break;
                case 'record-form'  :
                    $this->get_record_form();
                    break;
                case 'record-save'  :
                    $this->post_record_save();
                    break;
                default :
                    $this->show_error();
            }
        } else {
            $this->twiggy->template($this->module.'index')->display();
        }
    }

    /**
     * Private Function
     */
    private function show_error()
    {
        $this->twiggy->template('error/404')->display();
    }

    private function get_record_index()
    {
        $this->twiggy->template('asset/fixed/index')->display();
    }

    private function get_record_form()
    {
        $this->twiggy->template('asset/fixed/form')->display();
    }

    private function post_record_save()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('asset-code', 'required', 'required');
        $this->validation->set_rules('asset-status', 'required', 'required');
        $this->validation->set_rules('catalog-code', 'required', 'required');
        $this->validation->set_rules('material-name', 'required', 'required');
        $this->validation->set_rules('com-group', 'required', 'required');
        $this->validation->set_rules('adm-uom', 'required', 'required');
        $this->validation->set_rules('acquisition-cost', 'required', 'required');
        $this->validation->set_rules('acquisition-date', 'required', 'required');

        if($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("AssetHeader", "model");

            $key = false;
            $data = array(
                'asset_code' => $this->input->post('asset-code'),
                'asset_status' => $this->input->post('asset-status'),
                'catalog_code' => $this->input->post('catalog-code'),
                'material_name' => $this->input->post('material-name'),
                'com_group' => $this->input->post('com-group'),
                'adm_uom' => $this->input->post('adm-uom'),
                'acquisition_cost' => $this->input->post('acquisition-cost'),
                'acquisition_date' => date('Y-m-d H:i:s', strtotime($this->input->post('acquisition-date'))),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude')
            );

            if(strlen($this->input->post('asset_id')) > 0) {
                $key = $this->input->post('asset_id');
            }

            $this->model->save($data, $key);
        }

        redirect('asset/fixed');
    }
}