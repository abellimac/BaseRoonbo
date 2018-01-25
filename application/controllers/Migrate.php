<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller {

  public function index()
  {
    $this->load->library('migration');

    // if ( ! $this->migration->current()) {
    if ( ! $this->migration->latest()) {
      show_error($this->migration->error_string());
    }
    else
    {
      echo "Migrate executed success!";
      // https://www.screencast.com/t/Yppc4HyXFV
    }
  }
}