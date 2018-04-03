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

    /** Catalog */
    private function catalog_lookup()
    {
        // Load database
        $this->load->model("commodity/ComCatalog", "catalog");

        $this->twiggy->template('asset/fixed/lookup')->display();
    }

    public function list_catalog_get()
    {
//        if(!$this->input->is_ajax_request()){
//            $this->twiggy->template('error/error')->display();
//            return false;
//        }

        $this->load->database();
        $this->load->model("commodity/ComCatalog", "model");

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
            $row[] = '<button type="button" onclick="setValues(\''.$item->com_catalog_code.'\', \''.material_name($item->com_group, false).'\', \''.group_name($item->com_group).'\', \''.$item->com_group.'\', \''.UoM($item->adm_uom).'\', \''.$item->adm_uom.'\')">Pilih</button>';

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

    /** Fixed Asset */
    private function form_asset($param)
    {
        // Load database

        $this->twiggy->template('asset/fixed/form')->display();
    }

    /** Default Modal */
    public function modal_get($param, $arg = false)
    {
        if(!$this->input->is_ajax_request()){
            $this->twiggy->template('error/error')->display();
            return false;
        }

        switch ($param) {
            case 'fixed-asset'  :
                $this->form_asset($arg);
                break;
            case 'catalog-lookup'  :
                $this->catalog_lookup();
                break;
            default :
                $this->show_error();
        }
    }
}