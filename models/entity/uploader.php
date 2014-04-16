<?php

class uploader extends model {

    public function buildEditForm() {
        echo "<br />
			<form action='?type=uploader&action=upload' method='post' enctype='multipart/form-data'>
				<label for='file'>File:</label>
				<input type='file' name='file' id='file' /> 
				<br />
				<input type='submit' class='btn btn-success btn-large' value='Upload file' />
			</form>
			<br /><hr /><br />
        ";
    }
    
    public function displayManager($action, $parent, $child=null, $user, $auth=null) {
    	$this->buildEditForm();
    	
    	switch($action) {
    		case "delete":
	    		$fileData = getRecords($this->conn, "upload", array("*"), "id=$parent");
	    		
	    		//If the file exists, delete it
	    		if($fileData != false) {
		    		$data = mysqli_fetch_assoc($fileData);
		
		    		if(file_exists("custom/uploads//" . $data["file_name"])) {
		    			unlink("custom/uploads/" . $data["file_name"]);
		    		} else {
		    			echo "Could not find file on server. Removing record...<br />";
		    		}
		    		
		    		
		    		$sql = "DELETE FROM upload WHERE id=$parent";
		    		
		    		$result = $this->conn->query($sql) OR DIE ("Could delete file!");

		    		echo "File deleted successfully.<br /><br />";
	    		} else {
	    			echo "Could not delete file. Reason: Could not find file in database with ID: $parent!<br /><br />";
	    		}
	    		break;
    		case "upload":
    			if(isset($_FILES["file"])) {
			    	if ($_FILES["file"]["error"] > 0) {
			    		if($_FILES["file"]["error"] == 4)
							echo "Please select a file before pressing the submit button.<br /><br />";
			    	}
			    	else {
			    		$name = $_FILES["file"]["name"];
			    		
			    		if ( !is_dir( 'custom/uploads/' ) ) mkdir ('custom/uploads/', 0777, true);

			    		if (file_exists("custom/uploads/" . $name)) {
			    			$name = $this->nextName($_FILES["file"]["name"]);
			    		}

		    			move_uploaded_file($_FILES["file"]["tmp_name"], "custom/uploads/" . $name);
		    			echo "File uploaded successfully!<br /><br />";
	
		    			$sql = "INSERT INTO upload (file_name, file_type, file_size, file_date, file_created) VALUES";
		    			$sql .= "('" . $name . "', '" . $_FILES["file"]["type"] . "', '" . $_FILES["file"]["size"] . "', '" . date('Y-m-d H:i:s') . "','" . time() . "')";
		    			
		    			$result = $this->conn->query($sql) OR DIE ("Could not write to file table!");    			
			    	}
    			}
		    	break;
    	}
    }
    
    /**
     * Generates a new name for a file
     * 
     * @param $name		The file name to replace
     * @param $nameType	How to generate the new name (num, hash)
     *
     */
    private function nextName($name, $nameType="num") {
    	$ret = $name;
    	$extChar = strrpos($name, ".");
    	switch($nameType) {
    		case "num":
    			$i = 1;
    			while(file_exists("custom/uploads/" . $name)) {
    				$name = substr_replace($ret, "_" . $i, $extChar, 0);
    				$i ++;
    			}
    			$ret = $name;
    			break;
    		case "hash":
    			$ext = substr($name, $extChar);

    			while(file_exists("custom/uploads/" . $name)) {
    				$name = unique_salt() . $ext;
    			}
    			$ret = $name;
    			break;
    	}
    	
    	return $ret;
    }
    
    /**
     * Builds the necessary tables for this object
     *
     */
    public function buildTable() {
    	/*Table structure for table `upload` */
    	$sql = "CREATE TABLE IF NOT EXISTS `upload` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `file_name` varchar(255) DEFAULT NULL,
		  `file_type` varchar(64) DEFAULT NULL,
		  `file_size` int(255) DEFAULT NULL,
		  `file_date` datetime DEFAULT NULL,
		  `file_created` varchar(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
    	$this->conn->query($sql) OR DIE ("Could not build table \"file\"");
    
    }
}

?>