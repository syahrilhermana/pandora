<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Process extends CI_Controller
{
    protected $module;
    public function __construct()
    {
        parent::__construct();

        // init twiggy
        $this->twiggy->title('Master | Process');
        $this->module = 'master/process/';
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
                    $this->get_list();
                    break;
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

    private function get_list()
    {
        // load database
        $this->load->database();
        $this->load->model("Process", "model");

        $output = array();
        $list = $this->model->get_list_parent();

        foreach ($list as $item) {
            $data = array(
                "adm_process_name"  => $item->adm_process_name,
            );

            $output[] = $data;
        }

        $this->twiggy->set('list', $output);
        $this->twiggy->template($this->module.'tree')->display();
    }

    private function get_form($id=false)
    {
        $this->twiggy->template($this->module.'form')->display();
    }

    private function parent_exists($id=false)
    {
        $exists = false;

        if($id && strlen($id) > 0) {
            // load database
            $this->load->database();
            $this->load->model("Process", "model");

            $process = $this->model->exists($id);

            if($process == 1)
                $exists = true;
        }

        return $exists;
    }

    private function merge_code($parent, $me)
    {
        // load database
        $this->load->database();
        $this->load->model("Process", "model");

        $object = $this->model->get($parent);

        return $object->com_process_code.'.'.$me;
    }

    private function count_child($parent)
    {
        // load database
        $this->load->database();
        $this->load->model("Process", "model");

        $count = $this->model->count_child_by_parent($parent);

        return $count;
    }

    private function save()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('adm_process_name', 'Required', 'required');

        if($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("Process", "model");

            $data = array(
                'adm_process_name' => $this->input->post('adm_process_name')
            );
            if(strlen($this->input->post('adm_process_id')) > 0) {
                $key = $this->input->post('adm_process_id');

                $exists = $this->model->exists("adm_process_id", $this->input->post('adm_process_id'));
                if($exists > 0) {
                    $object = $this->model->get($key);

                    if($object->adm_process_id != $this->input->post('adm_process_id')) {
                        $this->session->set_flashdata('error', 'Process ID already exist');
                        $this->session->keep_flashdata('error');

                        redirect('master/process');
                    }
                }
            }
            $this->model->save($data, $key);
        }

        redirect('master/process');
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
            $this->load->model("Process", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'process code not valid'
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