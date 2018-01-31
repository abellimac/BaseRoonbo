<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Table_Permission extends CI_Migration
{
	public function up()
	{

		$this->load->dbforge();

		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			)
		));

		$this->dbforge->add_key('id', TRUE);  
		$this->dbforge->create_table('permission');
	}
}