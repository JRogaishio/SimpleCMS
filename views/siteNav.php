<div class="cms_nav">
	<div class="cms_navItemTitle"><div id="cms_dash" class="icon-leaf icon-white cms_icon"></div><a href="admin.php" class="cms_navItemTitleLink">Dashboard</a></div>
	
	<?php 

	echo '<div><div class="cms_navItemTitle"><div id="cms_site" class="icon-cog icon-white cms_icon"></div>Website Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_site">
			<ul>';
			
			if($this->_USER->checkPermission('site', 'read', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=site&action=read" class="cms_navItemLink">View / Edit Site</a></li>';

			if($this->_USER->checkPermission('log', 'read', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=log" class="cms_navItemLink">View the log</a></li>';

			if($this->_USER->checkPermission('customkey', 'read', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=customkey&action=read" class="cms_navItemLink">View / Edit Keys</a></li>';
				
			if($this->_USER->checkPermission('customkey', 'insert', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=customkey&action=insert&p=" class="cms_navItemLink">Add a Key</a></li>';
			
			if($this->_USER->checkPermission('uploader', 'read', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=uploader" class="cms_navItemLink">Upload a file</a></li>';

			if($this->_USER->checkPermission('updater', 'read', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=updateDisplay" class="cms_navItemLink">Update CMS</a></li>';
						
			echo '</ul>
		</div>
	</div>';

	if($this->_USER->checkPermission('page', 'read', false)) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_page" class="icon-file icon-white cms_icon"></div>Page Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_page">
			<ul>';
			if($this->_USER->checkPermission('page', 'update', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=page&action=read" class="cms_navItemLink">View / Edit Pages</a></li>';
			if($this->_USER->checkPermission('page', 'insert', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=page&action=insert&p=" class="cms_navItemLink">Add a Page</a></li>';
			
			echo '</ul>
		</div>
	</div>';
	}
						
	if($this->_USER->checkPermission('post', 'read', false)) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_post" class="icon-comment icon-white cms_icon"></div>Post Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_post">
			<ul>';
	
			if($this->_USER->checkPermission('post', 'update', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=post&action=read" class="cms_navItemLink">View / Edit Posts</a></li>';
			
			if($this->_USER->checkPermission('post', 'insert', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=post&action=insert&p=&c=" class="cms_navItemLink">Add a Post</a></li>';
			
			echo '</ul>
		</div>	
	</div>';
	}
	
	if($this->_USER->checkPermission('account', 'read', false)) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_user" class="icon-user icon-white cms_icon"></div>User Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_user">
			<ul>';
	
			if($this->_USER->checkPermission('account', 'update', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=user&action=read" class="cms_navItemLink">View / Edit Users</a></li>';
			if($this->_USER->checkPermission('account', 'insert', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=user&action=insert&p=" class="cms_navItemLink">Add a User</a></li>';
			if($this->_USER->checkPermission('permission', 'update', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=permissiongroup&action=read" class="cms_navItemLink">View / Edit Permissions</a></li>';
			if($this->_USER->checkPermission('permission', 'insert', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=permissiongroup&action=insert&p=" class="cms_navItemLink">Add a Permission Group</a></li>';
			
			echo '</ul>
		</div>
	</div>';
	}
	
	if($this->_USER->checkPermission('template', 'read', false)) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_template" class="icon-tasks icon-white cms_icon"></div>Template Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_template">
			<ul>';
			if($this->_USER->checkPermission('template', 'update', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=template&action=read" class="cms_navItemLink">View / Edit Templates</a></li>';
			if($this->_USER->checkPermission('template', 'insert', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=template&action=insert&p=" class="cms_navItemLink">Add a Template</a></li>';
			
			echo '</ul>
		</div>	
	</div>';
	}
	
	if($this->_USER->checkPermission('plugin', 'read', false)) {
	echo '<div><div class="cms_navItemTitle"><div id="cms_plugin" class="icon-share icon-white cms_icon"></div>Plugin Manager</div>
		<div class="cms_navItemList" id="cms_navItemList_plug">
			<ul>';
			if($this->_USER->checkPermission('plugin', 'update', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=plugin&action=read" class="cms_navItemLink">View / Edit Plugins</a></li>';
			if($this->_USER->checkPermission('plugin', 'insert', false))
				echo '<li class="cms_navItem"><a href="admin.php?type=plugin&action=insert&p=" class="cms_navItemLink">Add a Plugin</a></li>';
			
			echo '</ul>
		</div>	
	</div>';
	}
	?>
	<div><div class="cms_navItemTitle"></div></div>
</div>
