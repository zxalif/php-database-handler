<?php
	// Check for session and cookies
	// and redirect to login page or admin panel
	// restrict area
	// anybody can access the database but no one can access the assets
	include('conn/conn.php');
	class SQLCreate{
		private $data = array();
		private $options = array();
		private function extractor($data=null){
			if(null === $data){
				$data = array();
			}
			$keys = array();
			$values = array();
			if(is_array($data) || is_object($data)){
				foreach ($data as $key => $value) {
					array_push($keys, $key);
					array_push($values, $value);
				}
			}
			return array($keys, $values);
		}
		function generate($className, $data=null, $type=null, $options=null, $bulk=false){
			if(null === $data){
				$data = array();
			}
			else{
				$this->data = $data;
			}
			$this->className = $className;
			if(null === $options){
				$this->options = array();
			}
			else{
				$this->options = $options;
			}
			if(null === $type){
				$this->type = 'insert';
			}
			else{
				$this->type = $type;
			}
			if($type === "insert"){
				$sql = 'INSERT INTO ' . $this->className;
				$key = '';
				$value = '';
				# code..
				if($bulk == false){
					list($keys, $values) = $this->extractor($this->data);
					for($i = 0; $i < count($keys); $i++){
						if($i == count($keys)-1){
							$key = $key . $keys[$i] . ')';
							$value = $value . $values[$i] . '")';
						}
						elseif($i==0){
							$key = $key . '(' . $keys[$i] . ', ';
							$value = $value . '("' . $values[$i] . '", "';
						}
						else{
							$key = $key . $keys[$i] . ', ';
							$value = $value . $values . '", ';
						}
					}
					$sql = 'INSERT INTO ' . $this->className . $key . ' VALUES' . $value ;
				}
				return $sql;
			}
			elseif ($type === "view") {
				if(count($this->options) == 0){
					$sql = 'SELECT * FROM ' . $this->className;
				}
				else{
					$sql = 'SELECT * FROM ' . $this->className;
					if(array_key_exists('where', $this->options)){
						if(count($this->options['where']) == 0){
							$this->where = array();
						}
						else{
							$this->where = $this->options['where'];
						}
					}
					else{
						$this->where = array();
					}
					list($name, $val) = $this->extractor($this->where);
					if(count($this->where) > 0){
						$sql = $sql . ' WHERE ';
						for($i = 0; $i < count($name); $i+=1){
							if($i === count($name)-1){
								$sql = $sql . $name[$i] . '="' . $val[$i] . '"';
							}
							else{
								$sql = $sql . $name[$i] . '="' . $val[$i] . '" AND';
							}
						}
					}
				}
				if(array_key_exists('sort', $this->options)){
					if(is_array($this->options['sort']) || is_object($this->options['sort'])){
						if(count($this->options['sort']) == 1){
							$sql = $sql . ' ORDER BY ' . $this->options['sort'][0];
						}
					}
				}
				if(array_key_exists('limit', $this->options)){
					$sql = $sql . ' LIMIT ' . $this->options['limit'];
				}
				return $sql;
			}
			elseif ($type === "delete") {
				# code...
				return true;
			}
			elseif ($type === "update") {
				# code...
				return true;
			}
			elseif ($type === "create") {
				# code...
			}
			else{
				return false;
			}
		}
	}
	
	class CMSInsert extends SQLCreate{
		private $connection = null;
		private $data = null;
		private $className = null;
		private $error = null;
		private $SQL = null;
		function __construct($connection){
			$this->connection = $connection;
			//parent::__construct($this->className, $this->data);
		}
		function insert($className, $data, $bulk=false){
			$this->className = $className;
			$this->data = $data;
			if(!is_null($this->connection)){
				$this->SQL = $this->generate($this->className, $this->data, 'insert', $bulk);
				$result = mysqli_query($this->connection, $this->SQL);
				if(!$result){
					$this->error = mysqli_error($this->connection);
				}
				return $result;
			}
			return false;
		}
		protected function getError(){
			return $this->error;
		}
		protected function getSQL(){
			return $this->SQL;
		}
	}
	class CMSDBView extends CMSInsert{
		// class for view table and get data.
		private $setC = false;
		private $connection = null;
		private $className = null;
		private $SQL = null;
		private $fields = array();
		private $error = null;
		function __construct($connection){
			$this->limit = 10;
			$this->connection = $connection;
			parent::__construct($this->connection);
		}
		function setColumns($columns){
			$this->columns = $columns;
			$setC = true;
		}
		function getResult($className, $options=null){
			if(null === $options){
				$options = array(); // set default parameters
			}
			$this->options = $options;
			if(array_key_exists('limit', $this->options)){
				$this->limit = $this->options['limit'];
			}
			else{
				$this->limit = 10;
			}
			if(array_key_exists('fielss', $this->options)){
				if(count($this->options['fields']) == 0){
					$this->fielss = array();
				}
				else{
					$this->fielss = $this->options['fields'];
				}
			}
			if(array_key_exists('where', $this->options)){
				if(count($this->options['where']) == 0){
					$this->where = array();
				}
				else{
					$this->where = $this->options['where'];
				}
			}
			else{
				$this->where = array();
			}
			$this->className = $className;
			$kwrgs = array();
			$kwrgs['where'] = $this->where;
			if(count($this->fields) == 0){
				$this->SQL = parent::generate($className, $data=array(), $type='view', $kwrgs);
				$result = mysqli_query($this->connection, $this->SQL);
				if(!$result){
					$this->error = mysqli_error($this->connection);
				}
				return $result;
			}
		}
		function getSQL(){
			if(is_null($this->className) && is_null($this->SQL)){
				return parent::getSQL();
			}
			else{
				return $this->SQL;
			}
		}
		function getError(){
			if(is_null($this->className) && is_null($this->SQL)){
				return parent::getError();
			}
			else{
				return $this->error;
			}
		}
	}

	class CMSUpdate extends CMSDBView{
		function __construct($connection){
			parent::__construct($connection);
		}
	}

	class CMSDelete extends CMSUpdate{
		function __construct($connection){
			parent::__construct($connection);
		}
	}

	class Controller extends CMSDelete{
		function __construct($connection, $debug=false){
			parent::__construct($connection);
			$this->debug = $debug;
		}
		function getError(){
			if($this->debug === true){
				return parent::getError();
			}
			else{
				return null;
			}
		}
		function getSQL(){
			if($this->debug){
				return parent::getSQL();
			}
		}
	}
	//$info = new SQLCreate();
	//echo $info->generate('table_name', array(), $type='view', array('sort'=>array('c_name')));
?>