<?php
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
		function generate($className, $data=null, $type=null, $options=null){
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
			if(array_key_exists('bulk', $this->options)){
				$this->bulk = $options['bulk'];
			}
			else{
				$this->bulk = false;
			}
			if($type === "insert"){
				$sql = 'INSERT INTO ' . $this->className;
				$key = '';
				$value = '';
				# code..
				if($this->bulk == false){
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
							$value = $value . $values[$i] . '", "';
						}
					}
					$sql = 'INSERT INTO ' . $this->className . $key . ' VALUES' . $value ;
				}
				elseif($this->bulk == true){
					// DATA 0 INDEX IS ARRAY KEYS
					// DATA 1 INDEX IS BULK DATUMN
					$sql = 'INSERT INTO ' . $this->className;
					$key = '';
					$keys = $this->data[0];					
					$reminder = 1;
					if(count($this->data) == 2 && is_array($this->data[0]) && is_array($this->data)){
						for($index = 0; $index < count($this->data[1]); $index++){
							$values = $this->data[1][$index];
							$value = '';
							for($i = 0; $i < count($keys); $i++){
								if($i == count($keys)-1){
									if($reminder == 1){
										$key = $key . $keys[$i] . ')';
									}
									$value = $value . $values[$i] . '")';
								}
								elseif($i==0){
									if($reminder == 1){
										$key = $key . '(' . $keys[$i] . ', ';
									}
									$value = $value . '("' . $values[$i] . '", "';
								}
								else{
									if($reminder == 1){
										$key = $key . $keys[$i] . ', ';
									}
									$value = $value . $values[$i] . '", "';
								}
							}
							if($reminder == 1){
								$sql = 'INSERT INTO ' . $this->className . $key . ' VALUES ' . $value ;
							}
							else{
								$sql = $sql . ', ' . $value;
							}
							
							$reminder += 1;
						}
					}
				}
				return $sql;
			}
			elseif ($type === "view") {
				// IF NO EXTRA OPTION THERE
				if(count($this->options) == 0){
					$sql = 'SELECT * FROM ' . $this->className;
				}
				else{
					// SELECT COUNT(*) FROM TABLE
					// COUNT THE ROW NUMBER
					if (array_key_exists('count', $this->options)){
						if(count($this->options['count']) == 0){
							$sql = 'SELECT * FROM ' . $this->className;
						}
						elseif(count($this->options['count']) == 1){
							if($this->options['count'][0] != false || $this->options['count'][0] != true){
								$sql = 'SELECT COUNT(*) AS ' . $this->options['count'][0] .  ' FROM ' . $this->className;
							}
							else{
								$sql = 'SELECT * FROM ' . $this->className;
							}

						}
						elseif (count($this->options['count']) == 2) {
							if($this->options['count'][1] === true){
								$sql = 'SELECT *, (SELECT COUNT(*) FROM ' . $this->className . ') AS ' . $this->options['count'][0] .  ' FROM ' . $this->className;
							}
							elseif($this->options['count'][1] === false){
								$sql = 'SELECT COUNT(*) AS ' . $this->options['count'][0] .  ' FROM ' . $this->className;
							}
							else{
								$sql = 'SELECT * FROM ' . $this->className;
							}
						}
						else{
							$sql = 'SELECT * FROM ' . $this->className;
						}

					}
					else{
						$sql = 'SELECT * FROM ' . $this->className;
					}
					
					// SELECT FROM WHERE
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
								$sql = $sql . $name[$i] . '="' . $val[$i] . '" AND ';
							}
						}
					}
					
					// IN BETWEEN
					if(array_key_exists('between', $this->options)){
						if(count($this->options['between']) == 0){
							$this->between = array();
						}
						else{
							$this->between = $this->options['between'];
						}
					}
					else{
						$this->between = array();
					}
					if(empty($this->where)){
						$sql = $sql . ' WHERE ';
					}
					else{
						$sql = $sql . ' AND ';
					}
					list($name, $val) = $this->extractor($this->between);
					if(count($this->between) > 0){
						for($i = 0; $i < count($name); $i+=1){
							if($i === count($name)-1){
								$sql = $sql . $name[$i] . ' BETWEEN "' . $val[$i][0] . '" AND "' . $val[$i][1] . '"';
							}
							else{
								$sql = $sql . $name[$i] . ' BETWEEN "' . $val[$i][0] . '" AND "' . $val[$i][1] . '" AND ';
							}
						}
					}
				}
				
				// GROUP BY
				if(array_key_exists('group', $this->options)){
					if(is_array($this->options['group']) || is_object($this->options['group'])){
						if(count($this->options['group']) == 1){
							$sql = $sql . ' GROUP BY (' . $this->options['group'][0] . ')';
						}
						else{
							for($index = 0; $index < count($this->options['group']); $index++){
								if($index == 0){
									$sql = $sql . ' GROUP BY ' . $this->options['group'][$index] . ', ';
								}
								elseif($index == count($this->options['group'])){
									$sql = $sql . $this->options['group'][$index];
								}
								else{
									$sql = $sql . $this->options['group'][$index] . ', ';
								}
							}
						}
					}
				}
				
				// ORDER BY
				if(array_key_exists('sort', $this->options)){
					if(is_array($this->options['sort']) || is_object($this->options['sort'])){
						if(count($this->options['sort']) == 1){
							$sql = $sql . ' ORDER BY ' . $this->options['sort'][0];
						}
						else{
							for($index = 0; $index < count($this->options['sort']); $index++){
								if($index == 0){
									$sql = $sql . ' ORDER BY ' . $this->options['sort'][$index] . ', ';
								}
								elseif($index == count($this->options['sort'])){
									$sql = $sql . $this->options['sort'][$index];
								}
								else{
									$sql = $sql  . $this->options['sort'][$index] . ', ';
								}
							}
						}
					}
				}
				
				// LIMIT
				if(array_key_exists('limit', $this->options)){
					if(!is_array($this->options['limit']) && !is_object($this->options)){
						$sql = $sql . ' LIMIT ' . $this->options['limit'];
					}
					elseif (is_array($this->options['limit'])) {
						if(count($this->options['limit']) == 2){
							$sql = $sql . ' LIMIT ' . $this->options['limit'][0] . ', ' . $this->options['limit'][1];
						}
					}
				}
				return $sql;
			}
			elseif ($type === "delete") {
				$sql = 'DELETE FROM ' . $this->className . ' ';
				if(is_array($this->options)){
					if(array_key_exists('where', $this->options)){
						if(is_array($this->options['where']) && count($this->options['where']) > 0){
							list($key, $value) = $this->extractor($this->options['where']);
							for($i = 0; $i < count($key); $i++){
								if($i == 0){
									if(count($key) > 1){
										$sql = $sql . 'WHERE ' . $key[$i] . '="' . $value[$i] . '" AND ';
									}
									else{
										$sql = $sql . 'WHERE ' . $key[$i] . '="' . $value[$i] . '"';
									}
									
								}
								elseif ($i == count($key)-1) {
									$sql = $sql . $key[$i] . '="' . $value[$i] . '"';
								}
								else{
									$sql = $sql . $key[$i] . '="' . $value[$i] . '" AND ';
								}
							}
						}
					}
				}
				# code...
				return $sql;
			}
			elseif ($type === "update") {
				$sql = 'UPDATE ' . $this->className . ' SET ';
				list($key, $value) = $this->extractor($data);
				for($i = 0; $i < count($key); $i++){
					if($i == count($key)-1){
						$sql = $sql . $key[$i] . '="' . $value[$i] . '"';
					}
					else{
						$sql = $sql . $key[$i] . '="' . $value[$i] . '", ';
					}
				}
				if(array_key_exists('u_id', $this->options)){
					if(count($this->options['u_id']) == 1){
						list($key, $value) = $this->extractor($this->options['u_id']);
						$sql = $sql . ' WHERE ' . $key[0] . '="' . $value[0] . '"';
					}
				}
				return $sql;
			}
			elseif ($type === "create") {
				# code...
			}
			else{
				return false;
			}
		}
	}
	
	// CLASS FOR INSERT DATA INTO DATABASE
	class CMSInsert extends SQLCreate{
		private $connection = null;
		private $data = null;
		private $className = null;
		private $error = null;
		private $errorNo = null;
		private $SQL = null;
		function __construct($connection){
			$this->connection = $connection;
			//parent::__construct($this->className, $this->data);
		}
		function insert($className, $data, $bulk=false){
			$this->className = $className;
			$this->data = $data;
			$options = array();
			$options['bulk'] = $bulk;
			if(!is_null($this->connection)){
				$this->SQL = $this->generate($this->className, $this->data, 'insert', $options);
				$result = mysqli_query($this->connection, $this->SQL);
				if(!$result){
					$this->error = mysqli_error($this->connection);
					$this->errorNo = mysqli_errno($this->connection);
				}
				return $result;
			}
			return false;
		}
		protected function getError(){
			return $this->error;
		}
		function getErrorNo(){
			return $this->errorNo;
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
			if(array_key_exists('classes', $this->options)){
				if($this->options['classes'] === true){
					$this->classes = true;
				}
				else{
					$this->classes = false;
				}
			}
			else{
				$this->classes = false;
			}
			if(array_key_exists('limit', $this->options)){
				$this->limit = $this->options['limit'];
			}
			else{
				$this->limit = null;
			}
			if(array_key_exists('fielss', $this->options)){
				if(count($this->options['fields']) == 0){
					$this->fielss = array();
				}
				else{
					$this->fielss = $this->options['fields'];
				}
			}
			else{
				$this->fields = array();
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
			if(array_key_exists('between', $this->options)){
				if(count($this->options['between']) == 0){
					$this->between = array();
				}
				else{
					$this->between = $this->options['between'];
				}
			}
			else{
				$this->between = array();
			}
			if(array_key_exists('sort', $this->options)){
				if(count($this->options['sort']) == 0){
					$this->sort = array();
				}
				else{
					$this->sort = $this->options['sort'];
				}
			}
			else{
				$this->sort = array();
			}
			
			if(array_key_exists('group', $this->options)){
				if(count($this->options['group']) == 0){
					$this->group = array();
				}
				else{
					$this->group = $this->options['group'];
				}
			}
			else{
				$this->group = array();
			}
			if(array_key_exists('count', $this->options)){
				$this->count = $this->options['count'];
			}
			else{
				$this->count = array();
			}
			$this->className = $className;
			$kwrgs = array();
			$kwrgs['where'] = $this->where;
			$kwrgs['sort'] = $this->sort;
			$kwrgs['group'] = $this->group;
			$kwrgs['count'] = $this->count;
			$kwrgs['between'] = $this->between;
			if(!is_null($this->limit)){
				$kwrgs['limit'] = $this->limit;
			}
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
		private $className = null;
		private $SQL = null;
		private $connection = null;
		private $options = null;
		private $kwrgs = array();
		private $where = array();
		private $error = null;
		private $errorNo = null;
		function __construct($connection){
			$this->connection = $connection;
			parent::__construct($this->connection);

		}

		function update($className=null, $data=null, $options=null){
			$this->className = $className;
			$this->options = $options;
			$kwrgs = array();
			if(array_key_exists('u_id', $this->options)){
				$kwrgs['u_id'] = $this->options['u_id'];
			}
			
			if(!is_null($this->connection)){
				$this->SQL = parent::generate($this->className, $data, 'update', $kwrgs);
				$result = mysqli_query($this->connection, $this->SQL);
				if(!$result){
					$this->error = mysqli_error($this->connection);
					$this->errorNo = mysqli_errno($this->connection);
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
		function getErrorNo(){
			if(is_null($this->className) && is_null($this->SQL)){
				return parent::getErrorNo();
			}
			else{
				return $this->errorNo;
			}
		}
	}

	class CMSDelete extends CMSUpdate{

		private $className = null;
		private $SQL = null;
		private $connection = null;
		private $options = null;
		private $kwrgs = array();
		private $where = array();
		private $error = null;
		private $errorNo = null;

		function __construct($connection){
			$this->connection = $connection;
			parent::__construct($connection);
		}

		function delete($className, $options=null){
			$this->className = $className;
			if(null == $options){
				$this->options = array();
			}
			else{
				$this->options = $options;
			}
			if(is_array($this->options)){
				if(array_key_exists('u_id', $this->options)){
					$kwrgs['where'] = $this->options['u_id'];
				}
			}
			
			if(!is_null($this->connection)){
				$this->SQL = parent::generate($this->className, null, 'delete', $kwrgs);
				$result = mysqli_query($this->connection, $this->SQL);
				if(!$result){
					$this->error = mysqli_error($this->connection);
					$this->errorNo = mysqli_errno($this->connection);
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

		function getErrorNo(){
			if(is_null($this->className) && is_null($this->SQL)){
				return parent::getErrorNo();
			}
			else{
				return $this->errorNo;
			}
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
		
		function getErrorNo(){
			if($this->debug === true){
				return parent::getErrorNo();
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
?>