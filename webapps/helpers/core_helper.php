<?php

/**
 * Load Group Commodity By Parent
 * @access  public
 * @param   integer
 * @return  array
 */
if ( ! function_exists('child'))
{
    function child($parent)
    {
        $sys =& get_instance();
        $sys->load->database();

        $query = "select * from com_group where com_group_parent = " . $parent;
        $builder = $sys->db->query($query);
        $output = array();

        foreach ($builder->result() as $item) {
            $data = array(
                "id"    => $item->com_group_id,
                "code"  => $item->com_group_code,
                "name"  => $item->com_group_name,
                "parent"  => $item->com_group_parent,
                "child" => count_child($item->com_group_id)
            );

            $output[] = $data;
        }

        return $output;
    }
}

/**
 * Count Group Commodity By Parent
 * @access  public
 * @param   integer
 * @return  integer
 */
if ( ! function_exists('count_child'))
{
    function count_child($parent)
    {
        $sys =& get_instance();
        $sys->load->database();

        $query = "select count(*) as count from com_group where com_group_parent = " . $parent;
        $builder = $sys->db->query($query);

        return $builder->row()->count;
    }
}

/**
 * Encrypt helper
 * @access  public
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('encrypt'))
{
    function encrypt($string)
    {
        $sys =& get_instance();
        $sys->load->library('encryption');

        $sys->encryption->initialize(
            array(
                'cipher'    => $sys->config->item('cipher'),
                'mode'      => $sys->config->item('mode'),
                'key'       => $sys->config->item('encryption_key')
            )
        );

        return $sys->encryption->encrypt($string);
    }
}

/**
 * Decrypt helper
 * @access  public
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('decrypt'))
{
    function decrypt($string)
    {
        $sys =& get_instance();
        $sys->load->library('encryption');

        $sys->encryption->initialize(
            array(
                'cipher'    => $sys->config->item('cipher'),
                'mode'      => $sys->config->item('mode'),
                'key'       => $sys->config->item('encryption_key')
            )
        );

        return $sys->encryption->decrypt($string);
    }
}

/**
 * Sequence helper
 * @access  public
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('nextval'))
{
    function nextval($table, $where, $value)
    {
        $sys =& get_instance();
        $sys->load->database();
        $nextval = 0;

        // get last sequence
        $query = "select count(*) as nextval from ".$table." where ".$where." = '".$value."'";
        $builder = $sys->db->query($query);
        $nextval = (integer) $builder->row()->nextval + 1;
        $nextval = str_pad($nextval, 4, '0', STR_PAD_LEFT);

        return $nextval;
    }
}

/**
 * Material Name helper
 * @access  public
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('material_name'))
{
    function material_name($code, $show = "full")
    {
        $sys =& get_instance();
        $sys->load->database();

        $query = "select a.com_group_name as material_name, 
                    b.com_group_name as group_name, 
                    c.com_group_name as category_name 
                  from com_group a, com_group b, com_group c
                  where a.com_group_parent = b.com_group_id
                  and b.com_group_parent = c.com_group_id
                  and a.com_group_id = " . $code;
        $builder = $sys->db->query($query);
        if($show == "full")
            $result = $builder->row()->category_name . " > " . $builder->row()->group_name . " > " . $builder->row()->material_name;
        else
            $result = $builder->row()->material_name;

        return $result;
    }
}

/**
 * UoM helper
 * @access  public
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('UoM'))
{
    function UoM($code)
    {
        $sys =& get_instance();
        $sys->load->database();

        $query = "select adm_uom_code as uom_code, adm_uom_name as uom_name 
                  from adm_uom
                  where adm_uom_id = " . $code;
        $builder = $sys->db->query($query);
        $result = $builder->row()->uom_code;

        return $result;
    }
}

/**
 * Get Root Group helper
 * @access  public
 * @param   integer
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('com_group'))
{
    function com_group($code, $next = false, $parent = false)
    {
        $sys =& get_instance();
        $sys->load->database();

        // get root
        $query = "select com_group_parent, com_group_id from com_group where com_group_id = " . $code;
        $builder = $sys->db->query($query);

        if($builder->row()->com_group_parent) {
            if($next) {
                $query = "select com_group_parent, com_group_id from com_group where com_group_id = " . $builder->row()->com_group_parent;
                $builder = $sys->db->query($query);
                $result = ($builder->row()->com_group_parent) ? $builder->row()->com_group_parent : $builder->row()->com_group_id;
            } else {
                $result = ($parent) ? $builder->row()->com_group_parent : $builder->row()->com_group_id;
            }
        } else {
            $result = $builder->row()->com_group_id;
        }

        return $result;
    }
}