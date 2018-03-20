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
                case 'add'  :
                    $this->get_form();
                case 'save' :
                    $this->save();
                case 'delete' :
                    return $this->delete();
                case 'api'  :
                    'on fire';
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

    private function get_form($id=false)
    {
        $this->twiggy->template($this->module.'form')->display();
    }

    private function save()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('type', 'required', 'required');
        $this->validation->set_rules('material-name', 'required', 'required');
        $this->validation->set_rules('catalog-code', 'required', 'required');
        $this->validation->set_rules('uom', 'required', 'required');
        $this->validation->set_rules('description', 'required', 'required');

        if($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("ComCatalog", "model");

            $key = false;
            $data = array(
                'com_catalog_code' => $this->input->post('catalog-code'),
                'adm_uom' => $this->input->post('uom'),
                'com_group' => decrypt($this->input->post('material-name')),
                'com_type' => $this->input->post('type'),
                'com_description' => $this->input->post('description'),
                'com_manufacture' => $this->input->post('manufacture'),
                'com_brand' => $this->input->post('brand'),
                'com_part_number' => $this->input->post('part-number'),
                'com_serial_number' => $this->input->post('serial-number'),
                'is_asset' => ($this->input->post('is-asset') == 'on') ? 'Y' : 'N'
            );

            // upload file
            $initialize = array(
                'upload_path'   => './data/catalog/',
                'allowed_type'  => 'jpg|png'
            );

            $this->load->library('upload', $initialize);

            if($this->upload->do_upload('file')) {
                $file = $this->upload->data();
                $data['com_image'] = $file['file_name'];
            } else {
                $this->session->set_flashdata('error', 'file not uploaded');
                $this->session->keep_flashdata('error');

                redirect('commodity/catalog');
            }

            $exists = $this->model->exists('com_catalog_code', $this->input->post('catalog-code'));
            if($exists > 0) {
                $this->session->set_flashdata('error', 'catalog code already exist');
                $this->session->keep_flashdata('error');

                redirect('commodity/catalog');
            }

            if(strlen($this->input->post('id')) > 0) {
                $key = $this->input->post('id');
            }

            $this->model->save($data, $key);
        }

        redirect('commodity/catalog');
    }

    private function delete()
    {
        $this->load->library('form_validation', NULL, 'validation');
        $response = array(
            'status'  => true,
            'message' => 'we are superman'
        );

        $this->validation->set_rules('token', 'Required', 'required');

        if($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("ComGroup", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'group code not valid'
                );
            } else {
                if($this->model->count_child_by_parent($key) > 0) {
                    $response = array(
                        'status'  => false,
                        'message' => 'constraint error'
                    );
                } else {
                    $this->model->delete($key);
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}