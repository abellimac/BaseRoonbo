<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Create_Table_Page extends CI_Migration
{
// migrations
	public function up()
	{

		$this->load->dbforge();

		$this->dbforge->add_field(array(
			'blog_id' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'blog_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
			),
			'blog_description' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
		));

		$this->dbforge->add_key('blog_id', TRUE);  
		$this->dbforge->create_table('blog');
	}

	public function down()
	{
		$this->load->dbforge();  
		$this->dbforge->drop_table('blog');
	}
}