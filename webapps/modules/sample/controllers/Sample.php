<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sample extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // init twiggy
        $this->twiggy->title('Sample');
    }

    public function index()
    {
        $this->twiggy->template('dashboard/index')->display();
    }

    public function test()
    {
        $this->twiggy->template('dashboard/test')->display();
    }
}