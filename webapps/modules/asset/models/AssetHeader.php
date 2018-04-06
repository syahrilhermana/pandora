<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AssetHeader extends CI_Model
{
    var $table = 'asset_header';
    var $primary_key = 'asset_id';
    var $column_order = array(null, 'asset_id', 'asset_code');
    var $column_search = array('asset_code', 'material_name');
    var $order = array('asset_id' => 'asc');
    var $deleted = array('deleted_at' => DateTime::ATOM);


    /**
     * Generator field for search table
     */
    private function _get_field_query()
    {
        $this->db->from($this->table);
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
            return $this->db->insert_id();
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
            return $this->db->get()->result();
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