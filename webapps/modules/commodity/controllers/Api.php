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

    /** Group Commodity */
    public function list_group_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("ComGroup", "model");

        $output = array();
        $key = decrypt($this->input->get('token'));
        $list = $this->model->get_list_childs('com_group_parent', $key);

        foreach ($list as $value) {
            $data = array(
                'id'    => encrypt($value->com_group_id),
                'name'  => $value->com_group_name,
                'target' => $value->com_group_id
            );

            $output[] = $data;
        }

        $this->set_response($output, REST_Controller::HTTP_OK);
    }


    /** Catalog Commodity */
    public function nextval_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("ComGroup", "model");

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

    public function catalog_post()
    {
        // load library
        $this->load->library('form_validation', NULL, 'validation');

        $this->validation->set_rules('type', 'required', 'required');
        $this->validation->set_rules('material-name', 'required', 'required');
        $this->validation->set_rules('catalog-code', 'required', 'required');
        $this->validation->set_rules('uom', 'required', 'required');
        $this->validation->set_rules('description', 'required', 'required');

        $output = array(
            'status' => false,
            'message' => 'Data not valid'
        );

        if ($this->validation->run() === true) {
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
                'upload_path' => './data/catalog/',
                'allowed_type' => 'jpg|png'
            );

            $this->load->library('upload', $initialize);

            if ($this->upload->do_upload('file')) {
                $file = $this->upload->data();
                $data['com_image'] = $file['file_name'];
            } else {
                $output = array(
                    'status' => false,
                    'message' => 'File not uploadeds'
                );

                $exists = $this->model->exists('com_catalog_code', $this->input->post('catalog-code'));
                if ($exists > 0) {
                    $output = array(
                        'status' => false,
                        'message' => 'Catalog code already exist'
                    );
                }

                if (strlen($this->input->post('id')) > 0) {
                    $key = $this->input->post('id');
                }

                $this->model->save($data, $key);
            }

            $output = array(
                'status' => true,
                'message' => 'success'
            );
        }

        $this->set_response($output, REST_Controller::HTTP_OK);
    }

    public function list_catalog_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("ComCatalog", "model");

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
            $row[] = $item->com_catalog_code;
            $row[] = $item->com_description;
            $row[] = '<div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i>Options<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                          <li>
                            <a href="javascript:void(0);" onclick="ajaxLoad(\''.site_url('commodity/api/modal/catalog_view?token=').$item->com_catalog_id.'\', \'View Catalog\')"><i class="fa fa-plus"></i>View Item</a>
                          </li>
                          <li>
                            <a href="javascript:void(0);"  onclick="ajaxLoad(\''.site_url('commodity/api/modal/catalog_form?token=').$item->com_catalog_id.'\')"><i class="fa fa-edit"></i>Edit Item</a>
                          </li>
                          <li>
                            <a href="javascript:void(0);" onclick="removeItem(\''.site_url('commodity/api/catalog_delete').'\', \''.encrypt($item->com_catalog_id).'\', \'datatables\', true)"><i class="fa fa-trash"></i>Remove Item</a>
                          </li>
                        </ul>
                      </div>';

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

    public function catalog_delete_post()
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
            $this->load->model("ComCatalog", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'catalog code not valid'
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
    public function list_uom_get()
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        $this->load->database();
        $this->load->model("AdmUom", "model");

        $length = (!empty($_GET['length'])) ? $_GET['length'] : 10;
        $start  = (!empty($_GET['start'])) ? $_GET['start'] : 0;
        $draw   = (!empty($_GET['draw'])) ? $_GET['draw'] : 10;
        $list = $this->model->get_list($length, $start);
        $data = array();
        $no   = $start;

        foreach ($list as $item)
        {
            $no++;
            $token = $item->adm_uom_code."~".$item->adm_uom_id."~".$item->adm_uom_name;
            $row = array();
            $row[] = $no;
            $row[] = $item->adm_uom_code;
            $row[] = $item->adm_uom_name;
            $row[] = '<button class="btn btn-primary btn-sm" onclick="uomEdit(\''.$token.'\')" >Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(\''.site_url('commodity/api/uom_delete').'\', \''.encrypt($item->adm_uom_id).'\', \'datatables\', true)" >Delete</button>';

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

    public function uom_delete_post()
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
            $this->load->model("AdmUom", "model");

            $key = decrypt($this->input->post('token'));
            $exists = $this->model->exists($key);

            if($exists == 0) {
                $response = array(
                    'status'  => false,
                    'message' => 'uom code not valid'
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
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        // load database
        $this->load->database();
        $this->load->model("ComGroup", "group");
        $this->load->model("AdmUom", "uom");
        $this->load->model("ComCatalog", "catalog");

        $exp = explode('_', $param);
        $template = 'commodity/'.$exp[0].'/'.$exp[1];

        $categories = $this->group->get_list_parent();
        $uom = $this->uom->get_list();

        if($exp[1] == 'form') {
            if(isset($_GET['token'])) {
                $this->twiggy->set('catalog', $this->catalog->get($this->input->get('token')));
            }

            $this->twiggy->set('categories', $categories);
            $this->twiggy->set('uom', $uom);

        } elseif ($exp[1] == 'view') {
            $this->twiggy->set('catalog', $this->catalog->get($this->input->get('token')));
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