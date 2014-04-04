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
	protected $id = null;
	protected $path = null;
	protected $file = null;
	protected $name = null;
	
	//Getters
	public function getId() {return $this->id;}
	public function getPath() {return $this->path;}
	public function getFile() {return $this->file;}
	public function getName() {return $this->name;}
	
	//Setters
	public function setId($val) {$this->id = $val;}
	public function setPath($val) {$this->path = $val;}
	public function setFile($val) {$this->file = $val;}
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
			
			$sql = "INSERT INTO plugins (plugin_path, plugin_file, plugin_created) VALUES";
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

			$sql = "UPDATE plugins SET
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
		
		$sql = "DELETE FROM plugins WHERE id=" . $this->id;
		$result = $this->conn->query($sql);
		
		return $result;
	}
	
	/**
	 * Loads the plugin object members based off the plugin id in the database
	 * 
	 * @param $pluginId	The plugin to be loaded
	 */
	public function loadRecord($pluginId) {
		if(isset($pluginId) && $pluginId != null) {
			
			$sql = "SELECT * FROM plugins WHERE id=$pluginId";
				
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
	public function buildEditForm($pluginId) {

		//Load the page from an ID
		$this->loadRecord($pluginId);

		echo "<div id='main_content'>";
		echo '
			<form action="admin.php?type=plugin&action=update&p=' . $this->id . '" method="post">

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
		
		echo "<div id='main_tools'>";
		echo "<h2>Admin Actions</h2><br /><br />";
		
		echo '<a href="admin.php" class="actionLink">Back to Home</a><br /><br />';
		echo "</div><div class='clear'></div>";
		
		
	}
	
	/**
	 * Display the plugin management page/ Work In Progress
	 *
	 */
	public function displayManager($action, $parent, $child, $user, $auth=null) {
		$this->loadRecord($parent);
		$ret = false;
		switch($action) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
						
					$this->storeFormValues($_POST);
						
					if($parent == null) {
						$this->insert();
						//Re-build the main page after creation
						$ret = true;
						$this->log->trackChange("plugin", 'add',$user->getId(),$user->getLoginname(), $this->name . " added");
					} else {
						$this->update($parent);
						//Re-build the page creation form once we are done
						$this->buildEditForm($parent);
						$this->log->trackChange("plugin", 'update',$user->getId(),$user->getLoginname(), $this->name . " added");
					}
				} else {
					// User has not posted the template edit form yet: display the form
					$this->buildEditForm($parent);
				}
				break;
			case "delete":
				$this->delete($parent);
				$ret = true;
				$this->log->trackChange("plugin", 'delete',$user->getId(),$user->getLoginname(), $this->name . " added");
				break;
			default:
				echo "Error with plugin manager<br /><br />";
				$ret = true;
		}
		return $ret;
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `plugins` */
		$sql = "CREATE TABLE IF NOT EXISTS `plugins` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `plugin_path` varchar(128) DEFAULT NULL,
		  `plugin_file` varchar(128) DEFAULT NULL,
		  `plugin_created` varchar(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		
		$this->conn->query($sql) OR DIE ("Could not build table \"plugins\"");
		
	}
}

?>
