<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Table_Page extends CI_Migration
{
// migrations
	public function up()
	{

		$this->load->dbforge();

		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'title_html_site' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
			),
			'language_site' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
			),
			'logo_site' => array(
				'type' => 'VARCHAR',
				'constraint' => '250',
			),
		));

		$this->dbforge->add_key('id', TRUE);  
		$this->dbforge->create_table('page');
	}

	public function down()
	{
		$this->load->dbforge();  
		$this->dbforge->drop_table('page');
	}
}