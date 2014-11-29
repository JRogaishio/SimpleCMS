<?php

/**
 * Class to handle website configuration information
 *
 * @author Jacob Rogaishio
 * 
 */
class site extends model
{
	//Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $title = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"title");
	protected $urlFormat = array("orm"=>true, "datatype"=>"varchar", "length"=>32, "field"=>"urlFormat");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set
		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['title'])) $this->setTitle(clean($this->conn, $params['title']));
		if(isset($params['urlFormat'])) $this->setUrlFormat(clean($this->conn, $params['urlFormat']));
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";
	
		if($this->getTitle() == "") {
			$ret = "Please enter a site name.";
		}
	
		return $ret;
	}
		
	/**
	 * Loads the site object members based off the site id in the database
	 * 
	 * @param $siteId	The site to be loaded
	 */
	public function loadRecord($p=null, $c=null) {
		if(isset($p) && $p != null) {
			$this->load($p);
			
			//Set a field to use by the logger
			$this->logField = $this->getTitle();
		}
	}
	
	/**
	 * Builds the admin editor form to update the site
	 * 
	 * @param $siteId	The site to be edited
	 */
	public function buildEditForm($siteId, $child=null, $user=null) {

		//Load the site from an ID
		$this->loadRecord($siteId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=site&action=read">Site List</a> > <a href="admin.php?type=site&action=update&p=' . $siteId . '">Site</a><br /><br />';

		echo '
			<form action="admin.php?type=site&action=' . (($this->getId() == null) ? "insert" : "update") . '&p=' . $this->getId() . '" method="post">

			<label for="title" title="This is ...">Site name:</label><br />
			<input name="title" id="title" type="text" maxlength="150" value="' . $this->getTitle() . '" />
			<div class="clear"></div>

			<label for="urlFormat" title="This is the link format">Link format:</label><br />
			<select name="urlFormat" id="urlFormat">
				<option selected value="' . $this->getUrlFormat() . '">-- ' .($this->getUrlFormat()=="clean"?"website.com/page/MyPage":($this->getUrlFormat()=="raw"?"website.com/index.php?p=MyPage":"ERROR - UNKNOWN FORMAT TYPE")) . ' --</option>
				<option value="clean">website.com/page/MyPage</option>
				<option value="raw">website.com/index.php?p=MyPage</option>
			</select>

			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($siteId) || $siteId == null) ? "Create" : "Update") . ' This Site!" /><br /><br />
			</form>
		';
	}
		
	/**
	 * Display the site manager
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=site&action=read">Site</a><br /><br />';
	
		$siteList = $this->loadArr(new site($this->conn, $this->log), "created:DESC");
		
		if (count($siteList)) {
			foreach($siteList as $site) {
				echo "
				<div class=\"site\">
				<h2>
				Site: <a href=\"admin.php?type=site&action=update&p=".$site->getId()."\" class=\"cms_siteEditLink\" >" . $site->getTitle() . "</a>
				</h2>
				</div>";
			}
		} else {
			echo "<p>No keys found!</p>";
		}
	}
		
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function populate() {
		/*Insert site data for `site` if we dont have one already*/
		if(countRecords($this->conn, $this->table) == 0) {
			$this->setTitle('My FerretCMS Website');
			$this->setUrlFormat('clean');
			$this->setCreated(time());
			$this->save();
		}
	}
	
	//Override base model functions to do nothing incase of URL mishaps
	public function insert($surpressNotify = false) {
		//Override to do nothing
		return false;
	}
	public function remove($surpressNotify = false) {
		//Override to do nothing
		return false;
	}
}

?>


