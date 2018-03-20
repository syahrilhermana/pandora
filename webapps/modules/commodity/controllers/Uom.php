<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Uom extends CI_Controller
{
    protected $module;
    public function __construct()
    {
        parent::__construct();

        // init twiggy
        $this->twiggy->title('Commodity | Unit of Measure');
        $this->module = 'commodity/uom/';
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
                case 'list' :
                    return $this->get_list();
                    break;
                case 'save' :
                    $this->save();
                    break;
                case 'delete' :
                    return $this->delete();
                    break;
                case 'api'  :
                    return 'on fire';
                    break;
                default :
                    $this->show_error();
                    break;
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

    private function save()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('code', 'Required', 'required');
        $this->validation->set_rules('name', 'Required', 'required');

        if($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("AdmUom", "model");

            $key = false;
            $data = array(
                'adm_uom_code' => $this->input->post('code'),
                'adm_uom_name' => $this->input->post('name')
            );

            if(strlen($this->input->post('id')) > 0) {
                $key = $this->input->post('id');

                $exists = $this->model->exists("adm_uom_code", $this->input->post('code'));
                if($exists > 0) {
                    $object = $this->model->get($key);

                    if($object->adm_uom_code != $this->input->post('code')) {
                        $this->session->set_flashdata('error', 'UoM code already exist');
                        $this->session->keep_flashdata('error');

                        redirect('commodity/uom');
                    }
                }
            }

            $this->model->save($data, $key);
        }

        redirect('commodity/uom');
    }
}