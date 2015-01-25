<?php
include_once('controllers/core.php');

/**
 * Ferret CMS public class to create live content pages
 * 
 * FerretCMS is a simple lightweight content management system using PHP and MySQL.
 * This CMS class is written purely in PHP and JavaScript.
 *
 * @author Jacob Rogaishio
 * 
 */
class pub extends core {

	/** 
	 * This function is called whenever the class is first initialized. This takes care of page routing
	 * 
	 * @param $mode		Either admin or user and determines how to display the CMS
	 *
	 */
	public function load () {
		$this->loadPlugins($this);
		
		$this->_LINKFORMAT = get_linkFormat($this->_CONN);
		
		//User view mode
		$this->load_page($this->_PARENT);
		
	}
	

	
	//USER VIEW FUNCTIONS ###############################################################################
	
	/**
	 * Loads the page and template for the live website
	 *
	 * @param $pSafeLink		The link used to access the page. Ex: p=blog
	 * 
	 */
	public function load_page($pSafeLink) {
		global $cms; //Make the CMS variable a global so the pages can reference it
		
		$page = new page($this->_CONN, $this->_LOG);
		$this->getScope('pageService')->setContext($page);
		
		//Load the page
		if(isset($pSafeLink) && $pSafeLink != null && $pSafeLink != "home" && strpos($pSafeLink,"SYS_") === false) {
			$pageSQL = "SELECT * FROM page WHERE safeLink=:safeLink";
			
			$stmt = $this->_CONN->prepare($pageSQL);
			$stmt->bindValue(':safeLink', $pSafeLink, PDO::PARAM_STR);
			$stmt->execute();
			
			$pageData = $stmt->fetch(PDO::FETCH_ASSOC);

			if(is_array($pageData)) {
				$page->loadRecord($pageData['id']);
			}
		} else if($pSafeLink == null || $pSafeLink == "" || $pSafeLink == "home") {
			//Page safelink is blank! Default to the homepage
			$page->loadRecord("home");
		}
		
		//Load the page
		if($page->getTemplateId() != "" && $page->getTemplateId() != null && strpos($pSafeLink,"SYS_") === false) {
			$templateSQL = "SELECT * FROM template WHERE id=:templateId";
			
			$stmt = $this->_CONN->prepare($templateSQL);
			$stmt->bindValue(':templateId', $page->getTemplateId(), PDO::PARAM_INT);
			$stmt->execute();
				
			$template = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if(is_array($template)) {
				//Load the template file
				$page->setTemplatePath($template['path']);
				require(TEMPLATE_PATH . "/" . $template['path'] . "/" . $template['filename']);
				$this->getScope('templateService')->setContext($template);
			}
		} else {
			//Check to see if the CMS has already been setup
			if(countRecords($this->_CONN,"account") == 0) {
				echo "<p style='font-family:arial;text-align:center;'><strong>Hello</strong> there! I see that you have no users setup.<br />
				<a href='admin.php'>Click here to redirect to the admin page to setup your CMS.</a>
				</p><br />";
			} else {
				require_once(loadErrorPage($pSafeLink));
			}
		}
	
	}
	
	/**
	 * The navigation bar to show on the user page
	 *
	 * @param $data		An array of all the safe links to display in the navigation. Ex home, blog, archive
	 * 
	 */
	public function load_navigation($data=array()) {

		echo "<ul class='cms_ul_nav'>";
		
		for($i=0;$i<count($data);$i++) {
			$pageSQL = "SELECT * FROM page WHERE safelink=:safelink";
			
			$stmt = $this->_CONN->prepare($pageSQL);
			$stmt->bindValue(':safelink', $data[$i], PDO::PARAM_STR);
			$stmt->execute();
			
			$pageData = $stmt->fetch(PDO::FETCH_ASSOC);

			if(is_array($pageData)) {
				echo "<li class='cms_li_nav' id='nav-$data[$i]'><a href='" . formatLink($this->_LINKFORMAT, $pageData['safeLink'])  . "'>" . $pageData['title'] . "</a></li>";
			}
		}
		
		echo "</ul>";
	}	
}

?>

