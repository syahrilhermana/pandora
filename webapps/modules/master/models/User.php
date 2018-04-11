<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Model
{
    var $table = 'user';
    var $primary_key = 'id_user';
    var $primary_key2 = 'user.id_user';
    var $column_order = array(null,'user.username','user.password','user.name','department.name','company.name',null);
    var $column_search = array('user.id_user,user.username,user.password,user.name,department.name,company.name');
    var $select_field = 'user.id_user,user.username,user.password,user.name,department.name AS department,company.name AS company';
    var $order = array('user.id_user' => 'asc');
    var $deleted = array('deleted_at' => DateTime::ATOM);


    /**
     * Generator field for search table
     */
    private function _get_field_query()
    {
        $this->db->select($this->select_field)
                 ->from($this->table)
                 ->join('department','department.id_dpt=user.department','LEFT')
                 ->join('company','company.id_cmp=user.company','LEFT');
        $i = 0;
        foreach ($this->column_search as $item)
        {
            if(!empty($_POST['search']['value']))
            {
                if($i===0)
                {
                    $this->db->group_start();
                    $this->db->like('LOWER(' . $item . ')',strtolower($_POST['search']['value']) );
                }
                else
                {
                    $this->db->or_like('LOWER(' . $item . ')',strtolower($_POST['search']['value']) );
                }
                if(count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }
        if(isset($_POST['order']))
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
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
            $savedata = $this->db->insert_id();
            log_message('DEBUG','LIHAT DATA SAVE USER EMPTY : ' . $savedata);
            return $savedata;
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
    public function get_list_parent($limit = FALSE, $offset = FALSE) {
        $this->_get_field_query();
        if ($limit) {
            return $this->db->where("User ID is null")->limit($limit, $offset)->get()->result();
        } else {
            return $this->db->where("com_group_parent is null")->get()->result();
        }
    }

    /**
     * Get a list of data without pagination options
     *
     * @param int limit
     * @param int offset
     * @return array object
     */
    public function get_list_childs($where, $value = FALSE) {
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

    function count_filtered()
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

    public function count_all_parent()
    {
        $this->db->from($this->table);
        $this->db->where("com_group_parent is null");
        return $this->db->count_all_results();
    }

    public function is_parent($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = $this->primary_key;
        }
        return $this->db->where($where, $value)->count_all_results($this->table);
    }

    public function count_child_by_parent($parent)
    {
        $this->db->from($this->table);
        $this->db->where("com_group_parent", $parent);
        return $this->db->count_all_results();
    }
}