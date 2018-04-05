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
 * Group Name helper
 * @access  public
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('group_name'))
{
    function group_name($code)
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
        $result = $builder->row()->group_name;

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

/**
 * Generate Barcode helper
 * @access  public
 * @param   integer
 * @param   mixed
 * @return  mixed
 */
if ( ! function_exists('barcode'))
{
    function barcode($randomcode, $barcode_type, $scale=6, $fontsize=18, $thickness=30,$dpi=72)
    {
        // CREATE BARCODE GENERATOR
        // Including all required classes
        require_once( APPPATH . 'libraries/barcodegen/BCGFontFile.php');
        require_once( APPPATH . 'libraries/barcodegen/BCGColor.php');
        require_once( APPPATH . 'libraries/barcodegen/BCGDrawing.php');

        // Including the barcode technology
        // Ini bisa diganti-ganti mau yang 39, ato 128, dll, liat di folder barcodegen
        require_once( APPPATH . 'libraries/barcodegen/BCGcode39.barcode.php');

        // Loading Font
        // kalo mau ganti font, jangan lupa tambahin dulu ke folder font, baru loadnya di sini
        $font = new BCGFontFile(APPPATH . 'libraries/font/Arial.ttf', $fontsize);

        // Text apa yang mau dijadiin barcode, biasanya kode produk
        $text = $randomcode;

        // The arguments are R, G, B for color.
        $color_black = new BCGColor(0, 0, 0);
        $color_white = new BCGColor(255, 255, 255);

        $drawException = null;
        try {
            $code = new BCGcode39(); // kalo pake yg code39, klo yg lain mesti disesuaikan
            $code->setScale($scale); // Resolution
            $code->setThickness($thickness); // Thickness
            $code->setForegroundColor($color_black); // Color of bars
            $code->setBackgroundColor($color_white); // Color of spaces
            $code->setFont($font); // Font (or 0)
            $code->parse($text); // Text
        } catch(Exception $exception) {
            $drawException = $exception;
        }

        /* Here is the list of the arguments
        1 - Filename (empty : display on screen)
        2 - Background color */
        $drawing = new BCGDrawing('', $color_white);
        if($drawException) {
            $drawing->drawException($drawException);
        } else {
            $drawing->setDPI($dpi);
            $drawing->setBarcode($code);
            $drawing->draw();
        }
        // ini cuma labeling dari sisi aplikasi saya, penamaan file menjadi png barcode.
        $filename_img_barcode = str_replace("/","-",$randomcode) .'_'.$barcode_type.'.png';
        // folder untuk menyimpan barcode
        $drawing->setFilename( FCPATH .'public/data/barcode/'. $filename_img_barcode);
        // proses penyimpanan barcode hasil generate
        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);

        return $filename_img_barcode;
    }
}