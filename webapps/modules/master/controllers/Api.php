<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Api extends REST_Controller
{
    protected $headers = array();

    public function __construct()
    {
        parent::__construct();

        $this->headers = apache_request_headers();
    }

    /** Process Master */
    public function nextval_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("Process", "model");

        $output = array();
        $newcode = "SYS";
        $key = decrypt($this->input->get('token'));
        $object = $this->model->get($key);

        if(isset($object->adm_process_id)) {
            $newcode = $object->adm_process_id;
        }

        $this->set_response($output, REST_Controller::HTTP_OK);
    }

    public function process_post()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('adm_process_name', 'required', 'required');

        $output = array(
            'status' => false,
            'message' => 'Data not valid'
        );

        if ($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("Process", "model");

            $key = false;
            $data = array(
                'adm_process_id' => $this->input->post('adm_process_id'),
                'adm_process_name' => $this->input->post('adm_process_name')
            );

                $exists = $this->model->exists('adm_process_id', $this->input->post('adm_process_id'));
                if ($exists > 0) {
                    $output = array(
                        'status' => false,
                        'message' => 'Adm. Process code already exist'
                    );
                }
                if (strlen($this->input->post('adm_process_id')) > 0) {
                    $key = $this->input->post('adm_process_id');
                }
                $this->model->save($data, $key);
            }

            $output = array(
                'status' => true,
                'message' => 'success'
            );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }

    public function list_process_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("Process", "model");
//        $token = $item->adm_process_id."~".$item->adm_process_name;
        $length = (!empty($_GET['length'])) ? $_GET['length'] : 10;
        $start  = (!empty($_GET['start'])) ? $_GET['start'] : 0;
        $draw   = (!empty($_GET['draw'])) ? $_GET['draw'] : 10;
        $list = $this->model->get_list($length, $start);
        $data = array();
        $no   = $start;

        foreach ($list as $item)
        {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->adm_process_name;
            $row[] = '<button class="btn btn-default btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal/process_view?token=').$item->adm_process_id.'\', \'View Process\')" >View</button>
                    <button class="btn btn-primary btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal/process_form?token=').$item->adm_process_id.'\')" >Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(\''.site_url('master/api/process_delete').'\', \''.encrypt($item->adm_process_id).'\', \'datatables\', true)" >Delete</button>';

            $data[] = $row;
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->model->count_all(),
            "recordsFiltered" => $this->model->count_filtered(),
            "data" => $data
        );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }

    public function process_delete_post()
    {
        $this->load->library('form_validation', NULL, 'validation');
        $response = array(
            'status'  => false,
            'message' => 'data not valid'
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
                $this->model->delete($key);

                $response = array(
                    'status'  => true,
                    'message' => 'delete success'
                );
            }
        }

        $this->set_response($response, REST_Controller::HTTP_OK);
    }
    public function role_delete_post()
    {
        $this->load->library('form_validation', NULL, 'validation');
        $response = array(
            'status'  => false,
            'message' => 'data not valid'
        );

        $this->validation->set_rules('token', 'Required', 'required');

        if($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("Role", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'role code not valid'
                );
            } else {
                $this->model->delete($key);

                $response = array(
                    'status'  => true,
                    'message' => 'delete success'
                );
            }
        }

        $this->set_response($response, REST_Controller::HTTP_OK);
    }

    /** UoM API Service */
    public function list_role_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("Role", "model");

        $length = (!empty($_GET['length'])) ? $_GET['length'] : 10;
        $start  = (!empty($_GET['start'])) ? $_GET['start'] : 0;
        $draw   = (!empty($_GET['draw'])) ? $_GET['draw'] : 10;
        $list = $this->model->get_list($length, $start);
        $data = array();
        $no   = $start;

        foreach ($list as $item)
        {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->adm_role_name;
            $row[] = '<button class="btn btn-default btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_role/role_view?token=').$item->adm_role_id.'\', \'View Role\')" >View</button>
                    <button class="btn btn-primary btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_role/role_form?token=').$item->adm_role_id.'\')" >Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(\''.site_url('master/api/role_delete').'\', \''.encrypt($item->adm_role_id).'\', \'datatables\', true)" >Delete</button>';

            $data[] = $row;
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->model->count_all(),
            "recordsFiltered" => $this->model->count_filtered(),
            "data" => $data
        );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }
    public function role_post()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('adm_role_name', 'required', 'required');

        $output = array(
            'status' => false,
            'message' => 'Data not valid'
        );

        if ($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("Role", "model");

            $key = false;
            $data = array(
                'adm_role_id' => $this->input->post('adm_role_id'),
                'adm_role_name' => $this->input->post('adm_role_name')
            );

            $exists = $this->model->exists('adm_role_id', $this->input->post('adm_role_id'));
            if ($exists > 0) {
                $output = array(
                    'status' => false,
                    'message' => 'Adm. Process code already exist'
                );
            }
            if (strlen($this->input->post('adm_role_id')) > 0) {
                $key = $this->input->post('adm_role_id');
            }
            $this->model->save($data, $key);
        }

        $output = array(
            'status' => true,
            'message' => 'success'
        );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }


    /** Default Modal */
    public function modal_get($param)
    {
//        if(!$this->input->is_ajax_request()){
//            $this->twiggy->template('error/error')->display();
//            return false;
//        }

        // load database
        $this->load->database();
        $this->load->model("Role", "role");
        $this->load->model("Process", "process");
//        $this->load->model("Role", "role");

        $exp = explode('_', $param);
        $template = 'master/'.$exp[0].'/'.$exp[1];

//        $role = $this->role->get_list();
//        $process = $this->process->get_list();


        if($exp[1] == 'form') {
            if(isset($_GET['token'])) {
                $this->twiggy->set('process', $this->process->get($this->input->get('token')));
            }

        } elseif ($exp[1] == 'view') {
            $this->twiggy->set('process', $this->process->get($this->input->get('token')));
        } else {
            $template = 'error/404';
        }

        $this->twiggy->template($template)->display();
    }

    /////// modal ROLE module ///////
    public function modal_role_get($param)
    {
//        if(!$this->input->is_ajax_request()){
//            $this->twiggy->template('error/error')->display();
//            return false;
//        }

        // load database
        $this->load->database();
        $this->load->model("Role", "role");
//        $this->load->model("Role", "role");

        $exp = explode('_', $param);
        $template = 'master/'.$exp[0].'/'.$exp[1];

//        $role = $this->role->get_list();
//        $process = $this->process->get_list();


        if($exp[1] == 'form') {
            if(isset($_GET['token'])) {
                $this->twiggy->set('role', $this->role->get($this->input->get('token')));
            }

        } elseif ($exp[1] == 'view') {
            $this->twiggy->set('role', $this->role->get($this->input->get('token')));
        } else {
            $template = 'error/404';
        }

        $this->twiggy->template($template)->display();
    }


    /** Sample JWT encode & decode */
    public function index_get()
    {
        $headers = apache_request_headers();

        foreach ($headers as $header => $value) {
            echo "$header: $value <br />\n";
        }
    }

    public function data_get()
    {
        $headers = apache_request_headers();

        $data = array();

        // If JWT authorization
        if(!isset($headers["Authorization"]) || empty($headers["Authorization"]))
        {
            $data['status'] = 'unauthorized to access';
            $data['code'] = '401';
            $this->response($data, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->guard->token_valid($headers["Authorization"]);

//		$this->guard->geterate_token("user_id");

        // Users from a data store e.g. database
        $users = array(
            array('id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'fact' => 'Loves coding'),
            array('id' => 2, 'name' => 'Jim', 'email' => 'jim@example.com', 'fact' => 'Developed on CodeIgniter'),
        );

        $id = $this->get('id');

        // If the id parameter doesn't exist return all the users

        if ($id === NULL)
        {
            // Check if the users data store contains users (in case the database result returns NULL)
            if ($users)
            {
                // Set the response and exit
                $this->response($users, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                // Set the response and exit
                $this->response(array(
                    'status' => FALSE,
                    'message' => 'No users were found'
                ), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

        // Find and return a single record for a particular user.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Get the user from the array, using the id as key for retreival.
        // Usually a model is to be used for this.

        $user = NULL;

        if (!empty($users))
        {
            foreach ($users as $key => $value)
            {
                if (isset($value['id']) && $value['id'] === $id)
                {
                    $user = $value;
                }
            }
        }

        if (!empty($user))
        {
            $this->set_response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response(array(
                'status' => FALSE,
                'message' => 'User could not be found'
            ), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function data_post()
    {
        // $this->some_model->update_user( ... );
        $message = array(
            'id' => 100, // Automatically generated by the model
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'message' => 'Added a resource'
        );

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function data_put()
    {
        // $this->some_model->update_user( ... );
        $message = array(
            'id' => 100, // Automatically generated by the model
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'message' => 'Added a resource'
        );

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function data_delete()
    {
        $id = (int) $this->get('id');

        // Validate the id.
        if ($id <= 0)
        {
            // Set the response and exit
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // $this->some_model->delete_something($id);
        $message = array(
            'id' => $id,
            'message' => 'Deleted the resource'
        );

        $this->set_response($message, REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }
}