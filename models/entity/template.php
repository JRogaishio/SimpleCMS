<?php

/**
 * Class to handle page templates
 *
 * @author Jacob Rogaishio
 * 
 */
class template extends model
{
	//Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $path = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"path");
	protected $filename = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"filename");
	protected $title = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"title");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		// Store all the parameters. phpORM uses PDO parameter strings to handle injection
		if(isset($params['path'])) $this->setPath($params['path']);
		if(isset($params['file'])) $this->setFilename($params['file']);
		if(isset($params['title'])) $this->setTitle($params['title']);
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";
		
		if($this->getPath() == "") {
			$ret = "Please enter a folder name in _template/.";
		} else if(strpos($this->getPath(), " ") !== false) {
			$ret = "The path cannot contain any spaces.";
		} else if($this->getFilename() == "") {
			$ret = "Please enter a file to load in template folder (ex. index.php).";
		} else if(strpos($this->getFilename(), " ") !== false) {
			$ret = "The file cannot contain any spaces.";
		} else if($this->getTitle() == "") {
			$ret = "Please enter a title.";
		}
	
		return $ret;
	}
	
	/**
	 * Loads the template object members based off the template id in the database
	 * 
	 * @param $templateId	The template to be loaded
	 */
	public function loadRecord($p=null, $c=null) {
		if(isset($p) && $p != null) {
			$templateResult = $this->load($p);

			//Set a field to use by the logger
			$this->logField = $this->getTitle();
		}
	
	}
	
	/**
	 * Builds the admin editor form to add / update templates
	 * 
	 * @param $templateId	The template to be edited
	 */
	public function buildEditForm($templateId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($templateId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=template&action=read">Template List</a> > <a href="admin.php?type=template&action=update&p=' . $templateId . '">Template</a><br /><br />';

		
		echo '
			<form action="admin.php?type=template&action=' . (($this->getId() == null) ? "insert" : "update") . '&p=' . $this->getId() . '" method="post">

			<label for="path" title="This is the name in _template">Template folder name:</label><br />
			<input name="path" id="path" type="text" maxlength="150" value="' . $this->getPath() . '" />
			<div class="clear"></div>

			<label for="file" title="This is the name of the template php file">Template filename:</label><br />
			<input name="file" id="file" type="text" maxlength="150" value="' . $this->getFilename() . '" />
			<div class="clear"></div>

			<label for="title" title="This is the name that will appear when selecting a template">Display name:</label><br />
			<input name="title" id="title" type="text" maxlength="150" value="' . $this->getTitle() . '" />
			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($templateId) || $templateId == null) ? "Create" : "Update") . ' This Template!" /><br /><br />
			' . ((isset($templateId) && $templateId != null) ? '<a href="admin.php?type=template&action=delete&p=' . $this->getId() . '"" class="deleteBtn">Delete This Template!</a><br /><br />' : '') . '
			</form>
		';
	}
	
	/**
	 * Display the list of all templates
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=template&action=read">Template List</a><br /><br />';
	
		$templateList = $this->loadArr(new template($this->conn, $this->log), "created:DESC");
		
		if (count($templateList)) {
			foreach($templateList as $template) {
				echo "
				<div class=\"template\">
					<h2>
					<a href=\"admin.php?type=template&action=update&p=".$template->getId()."\" title=\"Edit / Manage this template\" alt=\"Edit / Manage this template\" class=\"cms_pageEditLink\" >" . $template->getTitle() . "</a>
					</h2>
					<p>" . TEMPLATE_PATH . "/" . $template->getPath() . "/" . $template->getFilename() . "</p>
				</div>";
			}
		} else {
			echo "<p>No templates found!</p>";
		}	
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function populate() {
		/*Insert default data for `templates` if we dont have one already*/
		if(countRecords($this->conn, $this->table) == 0) {
			$this->setPath('default_example');
			$this->setFilename('index.php');
			$this->setTitle('Default Example Template');
			$this->setCreated(time());
			$this->save();
		}
		
	}
	
}

?>
