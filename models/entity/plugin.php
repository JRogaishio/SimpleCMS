<?php

/**
 * Class to handle plug-ins
 *
 * @author Jacob Rogaishio
 * 
 */
class plugin extends model
{
	// Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $path = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"path");
	protected $filename = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"filename");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	protected $name = null;
	
	//Getters
	/*public function getId() {return $this->id;}
	public function getPath() {return $this->path;}
	public function getFile() {return $this->file;}*/
	public function getName() {return $this->name;}
	
	//Setters
	/*public function setId($val) {$this->id = $val;}
	public function setPath($val) {$this->path = $val;}
	public function setFile($val) {$this->file = $val;}*/
	public function setName($val) {$this->name = $val;}
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['path'])) $this->path = clean($this->conn, $params['path']);
		if(isset($params['file'])) $this->file = clean($this->conn, $params['file']);
		$this->name = substr($this->file, 0, strpos($this->file, ".php"));
		$this->constr = true;
	}

	/**
	 * Inserts the current plugin object into the database
	 */
	public function insert() {
		if($this->constr) {
			
			$sql = "INSERT INTO " . $this->table . " (plugin_path, plugin_file, plugin_created) VALUES";
			$sql .= "('$this->path', '$this->file', '" . time() . "')";
			
			$result = $this->conn->query($sql) OR DIE ("Could not create plugin!");
			if($result) {
				echo "<span class='update_notice'>Created plugin successfully!</span><br /><br />";
			}
		} else {
			echo "Failed to load fornm data!";
		}
	}

	/**
	 * Updates the current plugin object in the database.
	 * 
	 * @param $plugId	The plugin Id to update
	 */
	public function update() {
	
		if($this->constr) {

			$sql = "UPDATE " . $this->table . " SET
			plugin_path = '$this->path', 
			plugin_file = '$this->file'
			WHERE id=" . $this->id . ";";
			
			$result = $this->conn->query($sql) OR DIE ("Could not update plugin!");
			if($result) {
				echo "<span class='update_notice'>Updated plugin successfully!</span><br /><br />";
			}

		} else {
			echo "Failed to load form data!";
		}

	}

	/**
	 * Deletes the current plugin object from the database.
	 * 
	 * @param $plugId	The plugin to be deleted
	 * 
	 * @return returns the database result on the delete query
	 */
	public function delete() {
		echo "<span class='update_notice'>Plugin deleted! Bye bye '$this->name', we will miss you.<br />Please be sure to update any pages that were using this plugin!</span><br /><br />";
		
		$sql = "DELETE FROM " . $this->table . " WHERE id=" . $this->id;
		$result = $this->conn->query($sql);
		
		return $result;
	}
	
	/**
	 * Loads the plugin object members based off the plugin id in the database
	 * 
	 * @param $pluginId	The plugin to be loaded
	 */
	public function loadRecord($pluginId, $c=null) {
		//Set a field to use by the logger
		$this->logField = &$this->name;
		
		if(isset($pluginId) && $pluginId != null) {
			
			$sql = "SELECT * FROM " . $this->table . " WHERE id=$pluginId";
				
			$result = $this->conn->query($sql);

			if ($result !== false && mysqli_num_rows($result) > 0 )
				$row = mysqli_fetch_assoc($result);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->path = $row['plugin_path'];
				$this->file = $row['plugin_file'];
				$this->name = substr($this->file, 0, strpos($this->file, ".php"));
			}
			
			$this->constr = true;
		}
	
	}
	
	/**
	 * Builds the admin editor form to add / update plugins
	 * 
	 * @param $pluginId	The plugin to be edited
	 */
	public function buildEditForm($pluginId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($pluginId);

		echo "<div id='main_content'>";
		echo '
			<form action="admin.php?type=plugin&action=' . (($this->id == null) ? "insert" : "update") . '&p=' . $this->id . '" method="post">

			<label for="path" title="This is the name in plugins">Plugin folder name:</label><br />
			<input name="path" id="path" type="text" maxlength="150" value="' . $this->path . '" />
			<div class="clear"></div>

			<label for="file" title="This is the name of the plugin php file">Plugin filename:</label><br />
			<input name="file" id="file" type="text" maxlength="150" value="' . $this->file . '" />
			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($pluginId) || $pluginId == null) ? "Create" : "Update") . ' This Plugin!" /><br /><br />
			' . ((isset($pluginId) && $pluginId != null) ? '<a href="admin.php?type=plugin&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Plugin!</a><br /><br />' : '') . '
			</form>
		';
		echo "</div>";
	}
	
	/**
	 * Display the list of all plugins
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=plugin&action=read">Plugin List</a><br /><br />';
	
		$sql = "SELECT * FROM " . $this->table . " ORDER BY plugin_created DESC";
		$result = $this->conn->query($sql);
	
		if ($result !== false && mysqli_num_rows($result) > 0 ) {
			while($row = mysqli_fetch_assoc($result) ) {
	
				$file = stripslashes($row['plugin_file']);
				$path = stripslashes($row['plugin_path']);
				$name = substr($file, 0, strpos($file, ".php"));
				if($name == null)
					$name = "ERROR WITH PLUGIN FILE NAME";
	
				echo "
				<div class=\"plugin\">
					<h2>
					<a href=\"admin.php?type=plugin&action=update&p=".$row['id']."\" title=\"Edit / Manage this plugin\" alt=\"Edit / Manage this plugin\" class=\"cms_pageEditLink\" >$name</a>
						</h2>
						<p>" . PLUGIN_PATH . "/" . $path . "/" . $file . "</p>
				</div>";
	
			}
			} else {
			echo "
			<p>
				No plugins found!
			</p>";
		}
	
			}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `plugins` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `plugin_path` varchar(128) DEFAULT NULL,
		  `plugin_file` varchar(128) DEFAULT NULL,
		  `plugin_created` varchar(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
		
	}
}

?>
