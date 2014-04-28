<div class="cms_nav">
	<div class="cms_navItemTitle"><div id="cms_dash" class="icon-leaf icon-white cms_icon"></div><a href="admin.php" class="cms_navItemTitleLink">Dashboard</a></div>
	
	<?php 

	echo '<div><div class="cms_navItemTitle"><div id="cms_site" class="icon-cog icon-white cms_icon"></div>Website Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_site">
			<ul>';
			
			if($this->_USER->checkPermission('site', 'view'))
				echo '<li class="cms_navItem"><a href="admin.php?type=siteDisplay" class="cms_navItemLink">Edit Site</a></li>';

			if($this->_USER->checkPermission('log', 'view'))
				echo '<li class="cms_navItem"><a href="admin.php?type=log" class="cms_navItemLink">View the log</a></li>';

			if($this->_USER->checkPermission('customkey', 'view') && $this->_USER->checkPermission('customkey', 'edit'))
				echo '<li class="cms_navItem"><a href="admin.php?type=keyDisplay" class="cms_navItemLink">Edit Keys</a></li>';
				
			if($this->_USER->checkPermission('customkey', 'view') && $this->_USER->checkPermission('customkey', 'insert'))
				echo '<li class="cms_navItem"><a href="admin.php?type=key&action=insert&p=" class="cms_navItemLink">Add a Key</a></li>';
			
			if($this->_USER->checkPermission('uploader', 'view'))
				echo '<li class="cms_navItem"><a href="admin.php?type=uploader" class="cms_navItemLink">Upload a file</a></li>';

			if($this->_USER->checkPermission('updater', 'view'))
				echo '<li class="cms_navItem"><a href="admin.php?type=updateDisplay" class="cms_navItemLink">Update CMS</a></li>';
						
			echo '</ul>
		</div>
	</div>';

	if($this->_USER->checkPermission('page', 'view')) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_page" class="icon-file icon-white cms_icon"></div>Page Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_page">
			<ul>';
			if($this->_USER->checkPermission('page', 'edit'))
				echo '<li class="cms_navItem"><a href="admin.php?type=pageDisplay" class="cms_navItemLink">Edit Pages</a></li>';
			if($this->_USER->checkPermission('page', 'insert'))
				echo '<li class="cms_navItem"><a href="admin.php?type=page&action=insert&p=" class="cms_navItemLink">Add a Page</a></li>';
			
			echo '</ul>
		</div>
	</div>';
	}
						
	if($this->_USER->checkPermission('post', 'view')) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_post" class="icon-comment icon-white cms_icon"></div>Post Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_post">
			<ul>';
	
			if($this->_USER->checkPermission('post', 'edit'))
				echo '<li class="cms_navItem"><a href="admin.php?type=postDisplay" class="cms_navItemLink">Edit Posts</a></li>';
			
			if($this->_USER->checkPermission('post', 'insert'))
				echo '<li class="cms_navItem"><a href="admin.php?type=post&action=insert&p=&c=" class="cms_navItemLink">Add a Post</a></li>';
			
			echo '</ul>
		</div>	
	</div>';
	}
	
	if($this->_USER->checkPermission('user', 'view')) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_user" class="icon-user icon-white cms_icon"></div>User Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_user">
			<ul>';
	
			if($this->_USER->checkPermission('user', 'view'))
				echo '<li class="cms_navItem"><a href="admin.php?type=userDisplay" class="cms_navItemLink">Edit Users</a></li>';
			if($this->_USER->checkPermission('user', 'insert'))
				echo '<li class="cms_navItem"><a href="admin.php?type=user&action=insert&p=" class="cms_navItemLink">Add a User</a></li>';
			if($this->_USER->checkPermission('permission', 'edit'))
				echo '<li class="cms_navItem"><a href="admin.php?type=permissionDisplay" class="cms_navItemLink">Edit Permissions</a></li>';
			if($this->_USER->checkPermission('permission', 'insert'))
				echo '<li class="cms_navItem"><a href="admin.php?type=permission&action=insert&p=" class="cms_navItemLink">Add a Permission Group</a></li>';
			
			echo '</ul>
		</div>
	</div>';
	}
	
	if($this->_USER->checkPermission('template', 'view')) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_template" class="icon-tasks icon-white cms_icon"></div>Template Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_template">
			<ul>';
			if($this->_USER->checkPermission('template', 'edit'))
				echo '<li class="cms_navItem"><a href="admin.php?type=templateDisplay" class="cms_navItemLink">Edit Templates</a></li>';
			if($this->_USER->checkPermission('template', 'insert'))
				echo '<li class="cms_navItem"><a href="admin.php?type=template&action=insert&p=" class="cms_navItemLink">Add a Template</a></li>';
			
			echo '</ul>
		</div>	
	</div>';
	}
	
	if($this->_USER->checkPermission('plugin', 'view')) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_plugin" class="icon-share icon-white cms_icon"></div>Plugin Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_plug">
			<ul>';
			if($this->_USER->checkPermission('plugin', 'edit'))
				echo '<li class="cms_navItem"><a href="admin.php?type=pluginDisplay" class="cms_navItemLink">Edit Plugins</a></li>';
			if($this->_USER->checkPermission('plugin', 'insert'))
				echo '<li class="cms_navItem"><a href="admin.php?type=plugin&action=insert&p=" class="cms_navItemLink">Add a Plugin</a></li>';
			
			echo '</ul>
		</div>	
	</div>';
	}
	?>
	<div><div class="cms_navItemTitle"></div></div>
</div>
