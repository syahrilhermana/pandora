<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Group extends CI_Controller
{
    protected $module;
    public function __construct()
    {
        parent::__construct();

        // init twiggy
        $this->twiggy->title('Commodity | Group');
        $this->module = 'commodity/group/';
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
        $this->load->model("ComGroup", "model");

        $output = array();
        $list = $this->model->get_list_parent();

        foreach ($list as $item) {
            $data = array(
                "id"    => $item->com_group_id,
                "code"  => $item->com_group_code,
                "name"  => $item->com_group_name,
                "parent"  => $item->com_group_parent,
                "child" => $this->count_child($item->com_group_id)
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
            $this->load->model("ComGroup", "model");

            $group = $this->model->exists($id);

            if($group == 1)
                $exists = true;
        }

        return $exists;
    }

    private function merge_code($parent, $me)
    {
        // load database
        $this->load->database();
        $this->load->model("ComGroup", "model");

        $object = $this->model->get($parent);

        return $object->com_group_code.'.'.$me;
    }

    private function count_child($parent)
    {
        // load database
        $this->load->database();
        $this->load->model("ComGroup", "model");

        $count = $this->model->count_child_by_parent($parent);

        return $count;
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
            $this->load->model("ComGroup", "model");

            $key = false;
            $data = array(
                'com_group_code' => $this->input->post('code'),
                'com_group_name' => $this->input->post('name')
            );

            if($this->parent_exists($this->input->post('parent'))) {
                $data['com_group_parent'] = $this->input->post('parent');
                $data['com_group_code']   = $this->merge_code($this->input->post('parent'), $this->input->post('code'));
            }

            if(strlen($this->input->post('id')) > 0) {
                $key = $this->input->post('id');

                $exists = $this->model->exists("com_group_code", $this->input->post('code'));
                if($exists > 0) {
                    $object = $this->model->get($key);

                    if($object->com_group_code != $this->input->post('code')) {
                        $this->session->set_flashdata('error', 'group code already exist');
                        $this->session->keep_flashdata('error');

                        redirect('commodity/group');
                    }
                }
            }

            $this->model->save($data, $key);
        }

        redirect('commodity/group');
    }

    private function delete()
    {
        $this->load->library('form_validation', NULL, 'validation');
        $response = array(
            'status'  => false,
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
                    $response = array(
                        'status'  => false,
                        'message' => 'delete success'
                    );
                    $this->model->delete($key);
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}