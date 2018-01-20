<?php
class Model_Curso extends MY_Model 
{
	const TABLE_NAME = "curso";
	const ID_FIELD = "id_cur";
	const DELETE_FIELD = "deleted_cur";
	const COLUMN_SUFIX = '_cur';

	protected $id_cur;
	protected $nombre_cur;
	protected $cantidad_cur;


	public function setId_cur($id_cur)
	{
		$this->id_cur = $id_cur;
	}
	public function setNombre_cur($nombre_cur)
	{
		$this->nombre_cur = $nombre_cur;
	}
	public function setCantidad_cur($cantidad_cur)
	{
		$this->cantidad_cur = $cantidad_cur;
	}

	public function getId_cur()
	{
		return $this->id_cur;
	}
	public function getNombre_cur()
	{
		return $this->nombre_cur;
	}
	public function getCantidad_cur()
	{
		return $this->cantidad_cur;
	}
}