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

        if(isset($object->com_group_code)) {
            $newcode = $object->com_group_code;
        }

        $newcode .= '.'.nextval('com_catalog', 'com_group', $key);
        $output['newcode'] = $newcode;


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
                        'message' => 'Adm. Process ID already exist'
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
    //---------------------------------------------------------------------------------------------------------------//
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
                    'message' => 'Role ID already exist'
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


    //modal ROLE //
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
//------------------------------------------------------------------------------------------------------------------------------//
// COMPANY LIST//
    public function company_post()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('name', 'required', 'required');

        $output = array(
            'status' => false,
            'message' => 'Data not valid'
        );

        if ($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("Company", "model");

            $key = false;
            $data = array(
                'id_cmp' => $this->input->post('id_cmp'),
                'short_code' => $this->input->post('short_code'),
                'name' => $this->input->post('name')
            );

            $exists = $this->model->exists('id_cmp', $this->input->post('id_cmp'));
            if ($exists > 0) {
                $output = array(
                    'status' => false,
                    'message' => 'Company ID already exist'
                );
            }
            if (strlen($this->input->post('id_cmp')) > 0) {
                $key = $this->input->post('id_cmp');
            }
            $this->model->save($data, $key);
        }

        $output = array(
            'status' => true,
            'message' => 'success'
        );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }


    public function list_company_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("Company", "model");
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
            $row[] = $item->short_code;
            $row[] = $item->name;
            $row[] = '<button class="btn btn-default btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_company/company_view?token=').$item->id_cmp.'\', \'View Company\')" >View</button>
                    <button class="btn btn-primary btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_company/company_form?token=').$item->id_cmp.'\')" >Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(\''.site_url('master/api/company_delete').'\', \''.encrypt($item->id_cmp).'\', \'datatables\', true)" >Delete</button>';

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

    public function company_delete_post()
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
            $this->load->model("Company", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'Company ID not valid'
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
    // modal COMPANY module //
    public function modal_company_get($param)
    {
//        if(!$this->input->is_ajax_request()){
//            $this->twiggy->template('error/error')->display();
//            return false;
//        }

        // load database
        $this->load->database();
        $this->load->model("Company", "company");

        $exp = explode('_', $param);
        $template = 'master/'.$exp[0].'/'.$exp[1];


        if($exp[1] == 'form') {
            if(isset($_GET['token'])) {
                $this->twiggy->set('company', $this->company->get($this->input->get('token')));
            }

        } elseif ($exp[1] == 'view') {
            $this->twiggy->set('company', $this->company->get($this->input->get('token')));
        } else {
            $template = 'error/404';
        }

        $this->twiggy->template($template)->display();
    }

    //------------------------------------------------------------------------------------------------------------------------------//
// USER LIST //
    public function user_post()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('name', 'required', 'required');

        $output = array(
            'status' => false,
            'message' => 'Data not valid'
        );

        if ($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("User", "model");

            $key = false;
            $data = array(
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password'),
                'name' => $this->input->post('name'),
                'department' => $this->input->post('department'),
                'company' => $this->input->post('company')
            );

            $exists = $this->model->exists('id_user', $this->input->post('id_user'));
            if ($exists > 0) {
                $output = array(
                    'status' => false,
                    'message' => 'User ID already exist'
                );
            }
            if (strlen($this->input->post('id_user')) > 0) {
                $key = $this->input->post('id_user');
            }
            $this->model->save($data, $key);
        }

        $output = array(
            'status' => true,
            'message' => 'success'
        );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }


    public function list_user_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("User", "model");
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
            $row[] = $item->username;
            $row[] = $item->password;
            $row[] = $item->name;
            $row[] = $item->department;
            $row[] = $item->company;
            $row[] = '<button class="btn btn-default btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_user/user_view?token=').$item->id_user.'\', \'View User\')" >View</button>
                    <button class="btn btn-primary btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_user/user_form?token=').$item->id_user.'\')" >Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(\''.site_url('master/api/user_delete').'\', \''.encrypt($item->id_user).'\', \'datatables\', true)" >Delete</button>';

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

    public function user_delete_post()
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
            $this->load->model("User", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'User code not valid'
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
    // modal COMPANY module //
    public function modal_user_get($param)
    {
//        if(!$this->input->is_ajax_request()){
//            $this->twiggy->template('error/error')->display();
//            return false;
//        }

        // load database
        $this->load->database();
        $this->load->model("User", "user");
        $this->load->model("Company", "comp");
        $this->load->model("Department", "dept");

        $exp = explode('_', $param);
        $template = 'master/'.$exp[0].'/'.$exp[1];
        $dept = $this->dept->get_list();
        $comp = $this->comp->get_list();

        if($exp[1] == 'form') {
            if(isset($_GET['token'])) {
                $this->twiggy->set('user', $this->user->get($this->input->get('token')));
            }
            $this->twiggy->set('comp',$comp);
            $this->twiggy->set('dept',$dept);
        } elseif ($exp[1] == 'view') {
            $this->twiggy->set('user', $this->user->get($this->input->get('token')));
        } else {
            $template = 'error/404';
        }

        $this->twiggy->template($template)->display();
    }
//---------------------------------------------------------------------------------------------------------------------//
// DEPARTMENT LIST //

    public function department_post()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('name', 'required', 'required');

        $output = array(
            'status' => false,
            'message' => 'Data not valid'
        );

        if ($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("Department", "model");

            $key = false;
            $data = array(
                'id_dpt' => $this->input->post('id_dpt'),
                'short_code' => $this->input->post('short_code'),
                'name' => $this->input->post('name')
            );

            $exists = $this->model->exists('id_dpt', $this->input->post('id_dpt'));
            if ($exists > 0) {
                $output = array(
                    'status' => false,
                    'message' => 'Department ID already exist'
                );
            }
            if (strlen($this->input->post('id_dpt')) > 0) {
                $key = $this->input->post('id_dpt');
            }
            $this->model->save($data, $key);
        }

        $output = array(
            'status' => true,
            'message' => 'success'
        );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }


    public function list_department_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("Department", "model");

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
            $row[] = $item->short_code;
            $row[] = $item->name;
            $row[] = '<button class="btn btn-default btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_department/department_view?token=').$item->id_dpt.'\', \'View Department\')" >View</button>
                    <button class="btn btn-primary btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_department/department_form?token=').$item->id_dpt.'\')" >Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(\''.site_url('master/api/department_delete').'\', \''.encrypt($item->id_dpt).'\', \'datatables\', true)" >Delete</button>';

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

    public function department_delete_post()
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
            $this->load->model("Department", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'Department ID not valid'
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

    //modal//
    public function modal_department_get($param)
    {
        //        if(!$this->input->is_ajax_request()){
        //            $this->twiggy->template('error/error')->display();
        //            return false;
        //        }

        // load database
        $this->load->database();
        $this->load->model("Department", "department");

        $exp = explode('_', $param);
        $template = 'master/'.$exp[0].'/'.$exp[1];


        if($exp[1] == 'form') {
            if(isset($_GET['token'])) {
                $this->twiggy->set('department', $this->department->get($this->input->get('token')));
            }

        } elseif ($exp[1] == 'view') {
            $this->twiggy->set('department', $this->department->get($this->input->get('token')));
        } else {
            $template = 'error/404';
        }

        $this->twiggy->template($template)->display();
    }


//---------------------------------------------------------------------------------------------------------------------//
// ROOM LIST //

    public function room_post()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('name', 'required', 'required');

        $output = array(
            'status' => false,
            'message' => 'Data not valid'
        );

        if ($this->validation->run() === true) {
            // load database
            $this->load->database();
            $this->load->model("Room", "model");

            $key = false;
            $data = array(
                'id_room' => $this->input->post('id_room'),
                'name' => $this->input->post('name'),
                'short_code' => $this->input->post('short_code'),
                'department' => $this->input->post('department')
            );

            $exists = $this->model->exists('id_room', $this->input->post('id_room'));
            if ($exists > 0) {
                $output = array(
                    'status' => false,
                    'message' => 'Room ID already exist'
                );
            }
            if (strlen($this->input->post('id_room')) > 0) {
                $key = $this->input->post('id_room');
            }
            $this->model->save($data, $key);
        }

        $output = array(
            'status' => true,
            'message' => 'success'
        );

        $this->set_response($output, REST_Controller::HTTP_OK);
    }


    public function list_room_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("Room", "model");
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
            $row[] = $item->name;
            $row[] = $item->short_code;
            $row[] = $item->department;
            $row[] = '<button class="btn btn-default btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_room/room_view?token=').$item->id_room.'\', \'View Room\')" >View</button>
                    <button class="btn btn-primary btn-sm" onclick="ajaxLoad(\''.site_url('master/api/modal_room/room_form?token=').$item->id_room.'\')" >Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(\''.site_url('master/api/room_delete').'\', \''.encrypt($item->id_room).'\', \'datatables\', true)" >Delete</button>';

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

    public function room_delete_post()
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
            $this->load->model("Room", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'Room ID not valid'
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

    //modal//
    public function modal_room_get($param)
    {
                if(!$this->input->is_ajax_request()){
                    $this->twiggy->template('error/error')->display();
                    return false;
                }

        // load database
        $this->load->database();
        $this->load->model("Room", "room");
        $this->load->model("Department", "dept");


        $exp = explode('_', $param);
        $template = 'master/'.$exp[0].'/'.$exp[1];
        $dept = $this->dept->get_list();

        if($exp[1] == 'form') {
            if(isset($_GET['token'])) {
                $this->twiggy->set('room', $this->room->get($this->input->get('token')));
            }
            $this->twiggy->set('dept', $dept);

        } elseif ($exp[1] == 'view') {
            if(isset($_GET['token'])) {
            $this->twiggy->set('room', $this->room->get($this->input->get('token')));
            }
            $this->twiggy->set('dept', $dept);

        } else {
            $template = 'error/404';
        }

        $this->twiggy->template($template)->display();
    }
//-----------------------------------------------------------------------------------------------------------------------//
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