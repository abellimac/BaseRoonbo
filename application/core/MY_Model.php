<?php

class MY_Model
{
	const TABLE_NAME = null;
	const ID_FIELD = null;
	const DELETE_FIELD = null;
	const DELETE_STATE = 1;
	const COLUMN_SUFIX = null;

	public function __construct ()
	{
		$this->load->database();
		$deleteField = static::DELETE_FIELD;
		if ( $deleteField !== null )
		{
			$this->$deleteField = 0;
		}

		log_message( 'debug', 'Model Class Initialized' );
	}

	/**
	 * __get magic
	 *
	 * Allows models to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param    string $key
	 */
	public function __get ( $key )
	{
		// Debugging note:
		//	If you're here because you're getting an error message
		//	saying 'Undefined Property: system/core/Model.php', it's
		//	most likely a typo in your model code.
		return get_instance()->$key;
	}

	public function getId ()
	{
		static::validateTableDefinition();
		$idName = static::ID_FIELD;
		return $this->$idName;
	}

	/**
	 * @return self
	 */
	public static function getById ( $id )
	{
		static::validateTableDefinition();
		$ci = &get_instance();
		$ci->load->database();
		$sql = 'select * from ' . static::TABLE_NAME . ' where ' . static::ID_FIELD . ' = ' . $ci->db->escape( $id ) . ' and ' . static::DELETE_FIELD . " != 1";

		$query = $ci->db->query( $sql );
		return $query->row( 0, get_called_class() );
	}

	public function save ()
	{
		static::validateTableDefinition();
		$loggedUserId = Model_User::getLoggedUserId();
		$now = new DateTime();
		if ( $this->getId() !== null && $this->getId() !== "" )
		{
			$editedBy = "editedby" . static::COLUMN_SUFIX;
			$editedOn = "editedon" . static::COLUMN_SUFIX;
			$this->$editedBy = $loggedUserId;
			$this->$editedOn = $now->format( "Y-m-d H:i:s" );
			return $this->db->update( 
										static::TABLE_NAME, 
										get_object_vars( $this ), 
										array( static::ID_FIELD => $this->getId() ) 
									);
		}
		else
		{
			$createdBy = "createdby" . static::COLUMN_SUFIX;
			$createdOn = "createdon" . static::COLUMN_SUFIX;
			$this->$createdBy = $loggedUserId;
			$this->$createdOn = $now->format( "Y-m-d H:i:s" );
			$result = $this->db->insert( 
											static::TABLE_NAME, 
											get_object_vars( $this ) 
										);
			$idField = static::ID_FIELD;
			if ( $result === true )
			{
				$this->$idField = $this->db->insert_id();
			}
			return $this->$idField;
		}
	}

	public function delete ()
	{
		static::validateTableDefinition();
		$idField = static::ID_FIELD;
		$loggedUserId = Model_User::getLoggedUserId();
		$now = new DateTime();
		if ( static::hasLogicalDeletion() )
		{
			// Delete logically the row. Change the state.

			$editedBy = "editedby" . static::COLUMN_SUFIX;
			$editedOn = "editedon" . static::COLUMN_SUFIX;
			$this->$editedBy = $loggedUserId;
			$this->$editedOn = $now->format( "Y-m-d H:i:s" );
			$fields=array( static::DELETE_FIELD => static::DELETE_STATE,
							$editedBy=>$this->$editedBy,
							$editedOn=>$this->$editedOn);
			$result = $this->db->update( static::TABLE_NAME, $fields, array( static::ID_FIELD => $this->getId() ) );
			$this->$idField = null;
			return $result;
		}
		else
		{
			//Delete fisically the row.
			$result = $this->db->delete( static::TABLE_NAME, array( static::ID_FIELD => $this->getId() ) );
			$this->$idField = null;
			return $result;
		}
	}

	/**
	 * Return the list of errors.
	 *
	 * @return array List of errors.
	 */
	public function errorsDB ()
	{
		return $this->db->error();
	}

	protected static function validateTableDefinition ()
	{
		if ( static::TABLE_NAME === null )
		{
			throw new Exception( 'Model Exception: TABLE_NAME definition is not set' );
		}

		if ( static::ID_FIELD === null )
		{
			throw new Exception( 'Model Exception: ID_FIELD definition is not set' );
		}
	}

	public static function getDeleteConditionSql ()
	{
		$delCondition = ' 0 = 0 ';
		if ( static::hasLogicalDeletion() )
		{
			if ( is_string( static::DELETE_STATE ) )
			{
				$delCondition = ' ' . static::TABLE_NAME . '.' . static::DELETE_FIELD . ' != \'' . static::DELETE_STATE . '\' ';
			}
			else
			{
				$delCondition = ' ' . static::TABLE_NAME . '.' . static::DELETE_FIELD . ' != ' . static::DELETE_STATE . ' ';
			}
		}

		return $delCondition;
	}

	protected static function hasLogicalDeletion ()
	{
		if ( static::DELETE_FIELD !== null && static::DELETE_STATE !== null )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function toArray ()
	{
		return get_object_vars( $this );
	}

	public static function getDataTable ( $searchValue = "", $limit = 10, $offset = 0, $sortBy = "", $sortDir = "asc" )
	{
		static::validateTableDefinition();

		if ( empty( $sortBy ) )
		{
			$sortBy = static::ID_FIELD;
		}
		if ( $sortBy === null )
		{
			$sortBy = static::ID_FIELD;
		}
		$ci = &get_instance();
		$ci->load->database();

		$columnsToSearch = array_keys( get_class_vars( get_called_class() ) );

		$sql = 'select * from ' . static::TABLE_NAME;
		$sql .= ' where 0=0 ';
		if ( !empty( $searchValue ) )
		{
			$sql .= ' and (';
			foreach ( $columnsToSearch as $column )
			{
				$sql .= ' ' . $column . ' like ' . $ci->db->escape( "%" . $searchValue . "%" ) . ' or ';
			}
			$sql = substr( $sql, 0, -3 );
			$sql .= ")";
		}
		$sql .= ' and ' . static::getDeleteConditionSql() . ' order by ' . $ci->db->escape_str( $sortBy ) . ' ' . $ci->db->escape_str( $sortDir ) . ' limit ' . $ci->db->escape_str( $limit ) . ' offset ' . $ci->db->escape_str( $offset );
		$query = $ci->db->query( $sql );
		return $query->result_array();
	}

	public static function countDataTable ( $searchValue = "" )
	{
		static::validateTableDefinition();
		$ci = &get_instance();
		$ci->load->database();

		$sql = 'select count(' . static::ID_FIELD . ') as total from ' . static::TABLE_NAME;
		$sql .= ' where 0=0 ';
		$columnsToSearch = array_keys( get_class_vars( get_called_class() ) );

		if ( !empty( $searchValue ) )
		{
			$sql .= " and (";
			foreach ( $columnsToSearch as $column )
			{
				$sql .= ' ' . $column . ' like ' . $ci->db->escape( "%" . $searchValue . "%" ) . ' or ';
			}

			$sql = substr( $sql, 0, -3 );
			$sql .= ")";
		}
		$sql .= ' and ' . static::getDeleteConditionSql() . ' ';

		$query = $ci->db->query( $sql );
		return $query->row()->total;
	}

	/**
	 * @return self[]
	 */
	public static function getAll ( $limit = 10, $offset = 0 )
	{
		static::validateTableDefinition();
		$ci = &get_instance();
		$ci->load->database();

		$sql = 'select * from ' . static::TABLE_NAME . ' where ' . static::getDeleteConditionSql() . ' limit ' . $limit . ' offset ' . $offset;
		$query = $ci->db->query( $sql );
		return $query->result( get_called_class() );
	}

	public static function countAll ()
	{
		static::validateTableDefinition();
		$ci = &get_instance();
		$ci->load->database();

		$sql = 'select count(' . static::ID_FIELD . ') as total from ' . static::TABLE_NAME . ' where ' . static::getDeleteConditionSql();

		$query = $ci->db->query( $sql );
		return $query->row()->total;
	}

	protected static function recast ( $className, $object )
	{
		if ( !( $object instanceof stdClass ) )
		{
			return null;
		}
		if ( !class_exists( $className ) )
		{
			throw new InvalidArgumentException( sprintf( 'Inexistant class %s.', $className ) );
		}

		$new = new $className();

		foreach ( $object as $property => &$value )
		{
			$new->$property = &$value;
			unset( $object->$property );
		}
		unset( $value );
		$object = (unset) $object;
		return $new;
	}

	/**
	 * @return self[]
	 */
	public static function getAllByIds ( $idsArray )
	{
		$result = array();
		if ( count( $idsArray ) > 0 )
		{
			static::validateTableDefinition();
			$ci = &get_instance();
			$ci->load->database();
			$sql = 'select * from ' . static::TABLE_NAME . ' where ' . static::ID_FIELD . ' in (';

			foreach ( $idsArray as $id )
			{
				$sql .= $ci->db->escape( $id );
				if ( $id !== end( $idsArray ) )
				{
					$sql .= ", ";
				}
			}

			$sql .= ") and " . static::getDeleteConditionSql();

			$query = $ci->db->query( $sql );
			$result = $query->result( get_called_class() );;
		}
		return $result;
	}

	/**
	 * @return int the next id for the Model
	 */
	public static function getNextId ()
	{
		static::validateTableDefinition();
		$ci = &get_instance();
		$ci->load->database();

		$sql = 'select max(' . static::ID_FIELD . ') +1 as nextid from ' . static::TABLE_NAME;

		$query = $ci->db->query( $sql );
		return $query->row()->nextid;
	}


	/**
	 * Filters the field desired for output.
	 *
	 * @param $resultQuery array Get from query db
	 * @param $fieldsFilter array
	 *
	 * @return array Result filtered for JSON export.
	 */
	public static function filterOutputFromQueryResult ( $resultQuery, $fieldsFilter )
	{
		$finalResult = [ ];

		foreach ( $resultQuery as $resultItem )
		{
			if (!is_array( $resultItem )) {
				$resultItem = $resultItem->toArray();
			}

			$filterResultItem = [ ];
			$fields = array_keys( $fieldsFilter );

			foreach ( $fields as $key )
			{

				$labelToSet = $fieldsFilter[ $key ];

				if ( array_key_exists( $key, $resultItem ) )
				{
					$filterResultItem[ $labelToSet ] = $resultItem [ $key ];
				}
			}

			$finalResult[] = $filterResultItem;

		}

		return $finalResult;
	}

	/**
	 * Filters the field desired for output.
	 *
	 * @param $object array Get from Object parsed by toArray() method.
	 * @param $fieldsFilter array Fields to show with own label outputs.
	 *
	 * @return array Result filtered for JSON export.
	 */
	public static function filterOutputFromObject ( $object, $fieldsFilter )
	{

		$filterResultItem = [ ];
		$fields = array_keys( $fieldsFilter );

		foreach ( $fields as $key )
		{

			$labelToSet = $fieldsFilter[ $key ];

			if ( array_key_exists( $key, $object ) )
			{
				$filterResultItem[ $labelToSet ] = $object [ $key ];
			}
		}

		return $filterResultItem;
	}

	/**
	 * Validade unique field in the table.
	 *
	 * @param $arrField array Field and its value to validate.
	 *
	 * @return boolean .
	 */
	public function validateUniqueField ( $arrField )
	{
		static::validateTableDefinition();
		$ci = &get_instance();
		$ci->load->database();
		if ( $this->getId() !== null && $this->getId() !== "" )
		{
			$sql = 'select count(' . static::ID_FIELD . ') as exist 
					from ' . static::TABLE_NAME .
					' where ' . static::getDeleteConditionSql() . ' and '.
					$arrField[0].'='. $ci->db->escape( $arrField[1] ). ' and ' .static::ID_FIELD.'!='. $this->getId();

			$query = $ci->db->query( $sql );

			if ( $query->row()->exist > 0 )
			{
				return TRUE;
			}
			else
			{
				return FALSE;			
			}
		}
		else
		{
			$sql = 'select count(' . static::ID_FIELD . ') as exist 
					from ' . static::TABLE_NAME .
					' where ' . static::getDeleteConditionSql() . ' and '.
					$arrField[0]. '=' . $ci->db->escape( $arrField[1] );

			$query = $ci->db->query( $sql );
			
			if ( $query->row()->exist > 0 )
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
	}
	/**
	 * @param ajax_parameter, array that has values to insert in model
	 * @param mask, mask that transform the ajax_parameter $keys to the property name
	 * */
	public function loadModel( array $ajax_parameter, array $mask=array() )
	{
		if( !$mask )
		{
			foreach ( $ajax_parameter as $param => $value )
			{
				if( property_exists( $this, $param ) )
					$this->$param = $value;
			}
		}else
		{
			foreach ( $mask as $key => $value )
			{
				if( property_exists( $this, $value ) )
					$this->$value = $ajax_parameter[ $key ];
			}
		}
	}

	/**
	 * Use this method to start a transaction on DataBase.
	 * This transaction works on all and all models.
	 *
	 * @param bool $test Set the value TRUE to enabled test mode and it will automatically rollback the queries.
	 * @return bool
	 */
	public static function startTransaction ( $test = false )
	{
		$codeIgniterInstance = &get_instance();
		$codeIgniterInstance->load->database();

		return $codeIgniterInstance->db->trans_begin( $test );
	}

	/**
	 * Use this method to end a started transaction.
	 *
	 * @return bool
	 */
	public static function completeTransaction ()
	{
		$codeIgniterInstance = &get_instance();
		$codeIgniterInstance->load->database();

		return $codeIgniterInstance->db->trans_complete();
	}

	/**
	 * Use this method to know the status of the last transaction completed.
	 *
	 * @return bool TRUE if it was commited, FALSE if it was rolledback.
	 */
	public static function statusTransaction ()
	{
		$codeIgniterInstance = &get_instance();
		$codeIgniterInstance->load->database();

		return $codeIgniterInstance->db->trans_status();
	}

	/**
	 * Return the list of errors.
	 *
	 * @return array List of errors.
	 */
	public static function errorsTransaction ()
	{
		$codeIgniterInstance = &get_instance();
		$codeIgniterInstance->load->database();

		return $codeIgniterInstance->db->error();
	}
}