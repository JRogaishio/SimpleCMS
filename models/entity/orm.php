<?php 

/**
 * phpORM
 *
 * Class to handle CRUD operations via ORM
 *
 * @author Jacob Rogaishio
 *
 */
 class orm {
 	protected $table = null;
 	protected $conn = null;
 	
 	/**
 	 * Saves the class name and the database connection object
 	 * 
 	 * @param $conn	The database connection object
 	*/
 	public function __construct($conn) {
 		$this->table = get_class($this);
 		$this->conn = $conn;
 	}
 	
 	/**
 	 * Used as a getter / setter incase not already defined
 	 * 
 	 * @param $name			The name of the function called that doesn't exist
 	 * @param $arguments	Arguments sent to the function
 	 * 
 	 * @return Retuns true if the function was successful
 	 */
 	public function __call($name, $arguments)
 	{
 		$code = substr($name, 0, 3);
 	
 		if($code == "get") {
 			$var = substr($name, 3);
 			$var = lcfirst($var); //Set the first letter to lowercase for convention
 			
 			if(isset($this->$var)) {
 				return $this->get($this->$var);
 			} else {
 				return false;
 			}
 		} else if($code == "set") {
 			$var = substr($name, 3);
 			$var = lcfirst($var); //Set the first letter to lowercase for convention
 					
 			if(isset($this->$var) && isset($arguments[0])) {
 				$this->set($this->$var, $arguments[0]);
 				return true;
 			} else {
 				return false;
 			}
 		}
 	}
 	
 	
 	/**
 	 * Loads the object from the database based on an id
 	 * 
 	 * @param $id	The database ID to load
 	 * 
 	 * @return Returns true on database search success, else false
 	 */
 	public function load($id) {
 		$sql = "SELECT * FROM " . $this->table;
 		$primary = "";
 		$params = array();
 		foreach(get_object_vars($this) as $var) {
 			if(is_array($var) && isset($var['orm']) && $var['orm'] == true) {
 				if(isset($var['primary']) && $var['primary'] == true) {
 					$primary = $var['field'];
 					break;
 				}
 			}
 		}
 		if($id == "last") {
 			$sql .= " ORDER BY " . $primary . " DESC LIMIT 1";
 		} else if($id == "first") {
 			$sql .= " ORDER BY " . $primary . " ASC LIMIT 1";
 		} else {
 			$sql .= " WHERE " . $primary . "=:id";
 			$params['id'] = true;
 		}

 		$stmt = $this->conn->prepare($sql);
 		if(isset($params['id']))
 			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
 		
 		$result = $stmt->execute();

 		$row = $stmt->fetch(PDO::FETCH_ASSOC);
 		
 		//Set the loaded SQL data to the object ORM variables
 		if(is_array($row)) {
 			foreach(get_object_vars($this) as $var) {
 				if(is_array($var) && isset($var['orm']) && $var['orm'] == true && is_array($this->$var['field'])) {
 					$fieldValue = $row[$var['field']];
 					//Convert to a number based on my MySQL datatype, else if not a number it returns a string
 					$fieldValue = $this->convertNumber($fieldValue, $var['datatype']);

					$newVal = array("value"=>$fieldValue);
					$this->$var['field'] = array_merge($this->$var['field'], $newVal);	
 				}
 			}
 			
 		}
 		return $result;
 	}
 	
 	/**
 	 * Loads an array of objects from the database based on an id
 	 *
 	 * @param $relatedObject	A blank copy of the related object to clone
 	 * @param $sort				The sort order passed as field:type
 	 * @param $filters			Any filters sent as an array. Each filter should be field = value. You MUST have spaces between the comparison
 	 *
 	 * @return Returns true on database search success, else false
 	*/
 	public function loadArr($relatedObject, $sort = null, $filters=array()) {
 		$sortString = "";
 		$filterString = "";
 		
 		foreach($filters as $filter) {
 			if($filterString == "")
 				$filterString = " WHERE ";
 			else
 				$filterString .=  " AND ";
 			
 			$params = explode(' ', $filter);
 				 			
 			//Build the filter field compare :field
 			$filterString .= $params[0] . $params[1] . ' :' . $params[0];
 		}

 		if(strpos($sort, ":") !== false) {
 			$sortOrder = explode(":", $sort);
 			$sField = $sortOrder[0];
 			$sType = $sortOrder[1];
 			$sortString = " ORDER BY " . $sField . " " . $sType;
 		}
 		
 		$sql = "SELECT * FROM " . $relatedObject->table . $filterString . $sortString;

 		$relPrimary = null;
 		
 		//Find the related objects primary key field name
 		foreach(get_object_vars($relatedObject) as $var) {
 			if(is_array($var) && isset($var['orm']) && $var['orm'] == true) {
 				if(isset($var['primary']) && $var['primary'] == true) {
 					$relPrimary = $var['field'];
 					break;
 				}
 			}
 		}

 		$stmt = $this->conn->prepare($sql);
 		foreach($filters as $filter) {
			$params = explode(' ', $filter);
			//Get the PHP Array field
 			$field = $relatedObject->$params[0];
 			//Get the SQL field we want to access
 			$sqlField = $params[0];
 			//Get the condition value
 			$val = $params[2];
 			$type = $this->getDataType($field['datatype']);
			if($type=='str')
				$stmt->bindValue(':' . $sqlField, $val, PDO::PARAM_STR);
			else if($type=='int')
				$stmt->bindValue(':' . $sqlField, $val, PDO::PARAM_INT);
 		}

 		$stmt->execute();
 		$retArr = array();
 		
 		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

 		if(is_array($rows)) {
 			foreach($rows as $row) {
 				$obj = clone $relatedObject;
 				$obj->load($row[$relPrimary]);
 				array_push($retArr, $obj);
 			}
 		}
 		
 		return $retArr;
 	} 	
 	
 	/**
 	 * Deletes the object from the database based on an id
 	 *
 	 * @param $id	The database ID to delete
 	 *
 	 * @return Returns true on database delete success, else false
 	*/
 	public function delete() {
 		$sql = "DELETE FROM " . $this->table . " WHERE ";
 		$primary = null;
 		$primaryIndex = null;
 		
 		foreach(get_object_vars($this) as $var) {
 			if(is_array($var) && isset($var['orm']) && $var['orm'] == true) {
 				if(isset($var['primary']) && $var['primary'] == true && isset($var['value']) && $var['value'] != "") {
 					$primary = $var['field'];
 					$primaryIndex = $var['value'];
 					break;
 				}
 			}
 		}
 			
 		$sql .= $primary . "=" . $primaryIndex;
		if($primary != null && $primaryIndex != null)
 			$result = $this->conn->query($sql) OR DIE ("Could not load");
		else
			$result = false;
		
		return $result;
 	}
 	
 	/**
 	 * Sets a ORM object value
 	 * 
 	 *  @param &$var	The object to set the value, passed by reference
 	 *  @param $value	The value to set
 	 *  
 	*/
	public function set(&$var, $value) {
		$var['value'] = $value;
 	}
 	
 	/**
 	 * Gets a ORM object value
 	 * 
 	 * @param $var	The object to get a value from
 	 * 
 	 * @return Returns the value or null if none is set
 	*/
 	public function get($var) {
 		if(isset($var['value']))
 			return $var['value'];
 		else 
 			return null;
 	}
 	
 	/**
 	 * Saves the object to the database.
 	 * 
 	 * This will insert the object if no primary key is defined or update the database records if a key exists
 	 * 
 	 * @return Returns true if database success else false
 	*/
 	public function save() {
 		$primary = null;
 		$primaryIndex = null;
 		
 		$sql = "";
 		$field = "";
 		$value = "";
 		foreach(get_object_vars($this) as $var) {
 			if(is_array($var) && isset($var['orm']) && $var['orm'] == true) {
 				if(isset($var['primary']) && $var['primary'] == true && isset($var['value']) && $var['value'] != "") {
 					$primary = $var['field'];
 					$primaryIndex = $var['value'];
 				}
 			}
 		}
 		
 		//If there is a primary key, you are updating
 		if($primary != null) {
 			$sql = "UPDATE " . $this->table . " ";
 		} else {
 			//Insert since we dont have a key
 			$sql = "INSERT INTO " . $this->table . " ";
 		}
 		
 		foreach(get_object_vars($this) as $var) {
 			if(is_array($var) && isset($var['orm']) && $var['orm'] == true && isset($var['value'])) {
 				//Only build an insert / update for non-primary key fields
 				if(!isset($var['primary'])) {
			 		//If there is a primary key, you are updating
			 		if($primary != null) {
			 			if($value != "")
			 				$value .= ", ";
			 			else if($value == "")
			 				$value .= " SET ";
			 			
			 			$value .=  $var['field'] . "=" . $this->sqlWrap($var['value'], $var['datatype']);
			 		} else {
			 		//Insert since we dont have a key	
			 			
			 			if($field != "")
			 				$field .= ", ";
			 			if($value != "")
			 				$value .= ", ";
			 			
			 			$field .= $var['field'];
			 			$value .= $this->sqlWrap($var['value'], $var['datatype']);
			 		}
 				}
 			}
 		}
 		if($primary != null)
 			$sql .= $value . " WHERE " . $primary . "=" . $primaryIndex;
 		else 
 			$sql .= "(" . $field . ") VALUES (" . $value . ")";

 		$result = $this->conn->query($sql) OR DIE ("Could not save");
 		
 		return $result;
 	}
 	
 	/**
 	 * Determines if the datatype needs to be wrapped in single quotes when inserting / updating
 	 * 
 	 * @param $val	The value needing to be wrapped
 	 * @param $type	The datatype in the database
 	 * 
 	 * @return Returns the wrapped value if needed
 	*/
 	private function sqlWrap($val, $type) {
 		$wrappedTypes = array("CHAR", "VARCHAR", "TEXT", "TINYTEXT", "DATETIME");
 		
 		if(in_array(strtoupper($type), $wrappedTypes) == true) {
 			$val = "'" . $val . "'";
 		}
 		
 		return $val;
 	}
 	
 	/**
 	 * Determines if the SQL field is an int or string
 	 * 
 	 * @param $type	The datatype in the database
 	 * 
 	 * @return datatype of int or str
 	 */
 	private function getDataType($type) {
 		$ret = null;
 		$strTypes = array("CHAR", "VARCHAR", "TEXT", "TINYTEXT", "DATETIME");
 		$intTypes = array("BIGINT", "DECIMAL", "INT", "MEDIUMINT", "SMALLINT", "TINYINT");
 		
 		if(in_array(strtoupper($type), $intTypes) == true) {
 			$ret = 'int';
 		} else if(in_array(strtoupper($type), $strTypes) == true) {
 			$ret = 'str';
 		}
 		return $ret;
 	}
 	
 	/**
 	 * Determines if the datatype needs to be converted to a php number
 	 * 
 	 * @param $val	The value needing to be converted
 	 * @param $type	The datatype in the database
 	 * 
 	 * @return Returns the converted value if needed
 	*/
 	private function convertNumber($val, $type) {
 		$intTypes = array("BIGINT", "DECIMAL", "INT", "MEDIUMINT", "SMALLINT", "TINYINT");
 		$floatTypes = array("DOUBLE", "FLOAT");
 		$ret = null;
 		if(in_array(strtoupper($type), $intTypes) == true) {
 			$ret = intval($val);
 		} else if(in_array(strtoupper($type), $floatTypes) == true) {
 			$ret = floatval($val);
 		} else {
 			$ret = $val;
 		}
 			
 		return $ret;
 	}
 	
 	/**
 	 * Saves the object to the database as a table
 	 * 
 	 * @return Returns true if database success else false
 	 */
 	public function persist() {
 		$create = true;
 		$sql = "";
 		$field = "";
 		$pk = "";

 		//Table doesn't exist. Create it
 		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (";

 		foreach(get_object_vars($this) as $var) {
  			if(is_array($var) && isset($var['orm']) && $var['orm'] == true) {
  				if($field != "")
  					$field .= ', ';
  				
 				$field .= "`" . $var['field'] . "` " . $var['datatype'];
 				if(isset($var['length'])) {
 					$field .= "(" . $var['length'] . ")";
 				}
 				if(isset($var['primary']) && $var['primary'] == true) {
 					$field .= " NOT NULL AUTO_INCREMENT";
 					$pk .= $var['field'];;
 				} else {
 					$field .= " DEFAULT NULL";
 				}
 			}
 		}
 		
 		$sql .= $field;
 		
 		if($pk != "")
 			$sql .= ", PRIMARY KEY (`" . $pk . "`)";
 		
 		if($create)
 			$sql .= ");";
 		
 		$result = $this->conn->query($sql) OR DIE ("Could not save");
 		
 		return $result;
 	}
 	
 }
 
?>
