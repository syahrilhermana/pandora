<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mutation extends CI_Model
{
    var $table = 'asset_header';
    var $primary_key = 'asset_id';
    var $primary_key2 = 'asset_header.asset_id';
    var $column_order = array(null, 'asset_header.asset_id','asset_header.asset_code', 'asset_header.asset_status', 'asset_header.catalog_code', 'asset_header.material_name', 'com_group.com_group_name AS com_group', 'adm_uom.adm_uom_name AS adm_uom', 'asset_header.acquisition_cost', 'asset_header.acquisition_date', 'asset_header.depreciation', 'asset_header.actual_price', 'asset_header.status','asset_header.comtable', 'company.name', 'department.name', 'room.name', 'user.name');
    var $column_search = array('asset_header.asset_code', 'asset_header.asset_status', 'asset_header.catalog_code', 'asset_header.material_name', 'com_group.com_group_name AS com_group', 'adm_uom.adm_uom_name AS adm_uom', 'asset_header.acquisition_cost', 'asset_header.acquisition_date', 'asset_header.depreciation', 'asset_header.actual_price', 'asset_header.status','asset_header.comtable', 'company.name AS company', 'department.name AS department', 'room.name AS room', 'user.name AS user');
    var $select_field = 'asset_header.asset_id,asset_header.asset_code, asset_header.asset_status, asset_header.catalog_code, asset_header.material_name, com_group.com_group_name AS com_group,adm_uom.adm_uom_name AS adm_uom, asset_header.acquisition_cost, asset_header.acquisition_date, asset_header.depreciation, asset_header.actual_price, asset_header.status,asset_header.comtable, company.name AS company, department.name AS department, room.name AS room, user.name AS user';
    var $order = array('asset_header.asset_id' => 'asc');
    var $deleted = array('deleted_at' => DateTime::ATOM);


    /**
     * Generator field for search table
     */
    private function _get_field_query()
    {
        $this->db->select($this->select_field)
                 ->from($this->table)
                 ->join('department','asset_header.department = department.id_dpt','LEFT')
                 ->join('company','asset_header.company = company.id_cmp','LEFT')
                 ->join('room','asset_header.room = room.id_room','LEFT')
                 ->join('user','asset_header.user=user.id_user','LEFT')
                 ->join('com_group','asset_header.com_group = com_group.com_group_id','LEFT')
                 ->join('adm_uom','asset_header.adm_uom=adm_uom.adm_uom_id','LEFT');
        $i = 0;
        foreach ($this->column_search as $item)
        {
            if(!empty($_GET['search']['value']))
            {
                if($i===0)
                {
                    $this->db->group_start();
                    $this->db->like('LOWER(' . $item . ')',strtolower($_GET['search']['value']) );
                }
                else
                {
                    $this->db->or_like('LOWER(' . $item . ')',strtolower($_GET['search']['value']) );
                }
                if(count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }
        if(isset($_POST['order']))
        {
            $this->db->order_by($this->column_order[$_GET['order']['0']['column']], $_GET['order']['0']['dir']);
        }
        else if(isset($this->order))
        {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    /**
     * Save or Update data
     *
     * @param array object
     * @return int id
     */
    public function save($object, $id = FALSE) {
        if(!$id)
        {
            $this->db->insert($this->table, $object);
            $insert = $this->db->insert_id();
            log_message('DEBUG','save mutation'.$insert);
            return $insert;
        } else {
            $this->db->where($this->primary_key, $id)->update($this->table, $object);
            return $id;
        }
    }

    /**
     * Delete permanent data
     *
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function delete($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = $this->primary_key;
        }
        $this->db->where($where, $value)->delete($this->table);
    }

    /**
     * Soft Delete a data (just flag it)
     *
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function soft_delete($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = 'id';
        }
        $this->db->where($where, $value)->update($this->table, $this->deleted);
    }

    /**
     * Retrieve a data
     *
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function get($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = $this->primary_key;
        }
        $object = $this->db->where($where, $value)->get($this->table)->row();
        return $object;
    }

    /**
     * Get a list of data with pagination options
     *
     * @param int limit
     * @param int offset
     * @return array object
     */
    public function get_list_no_paging($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = $this->primary_key;
        }
        $object = $this->db->where($where, $value)->get($this->table)->result();
        return $object;
    }

    /**
     * Get a list of data with pagination options
     *
     * @param int limit
     * @param int offset
     * @return array object
     */
    public function get_list($limit = FALSE, $offset = FALSE) {
        $this->_get_field_query();
        if ($limit) {
            return $this->db->limit($limit, $offset)->get()->result();
        } else {
            $dataa = $this->db->get();
            log_message('DEBUG','DATA MUTASI ASSET = '. $dataa);
            return $dataa->result();
        }
    }

    /**
     * Check if a data exists
     *
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function exists($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = $this->primary_key;
        }
        return $this->db->where($where, $value)->count_all_results($this->table);
    }

    /**
     * Check if a data used in another table
     *
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function used($where, $value = FALSE, $reference = FALSE) {
        if (!$value) {
            $value = $where;
            $where = $this->primary_key;
        }
        if (!$reference) {
            $this->table = $reference;
        }
        return $this->db->where($where, $value)->count_all_results($this->table);
    }

    public function count_filtered()
    {
        $this->_get_field_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function currentval()
    {
        $sql = "SELECT SUBSTRING(asset_code, 15, 4) as currentval FROM asset_header ORDER BY asset_id DESC LIMIT 1";

        $builder = $this->db->query($sql);
        $result = (isset($builder->row()->currentval)) ? $builder->row()->currentval : 0;

        return $result;
    }
}