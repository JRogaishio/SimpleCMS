<?php

class updater extends model {

    public function displayManager() {
        echo "<br />
            <table>
                <tr><td>Current Version: </td><td><strong>" . SYSTEM_VERSION . "</strong></td></tr>
                <tr><td>Latest Version: </td><td><strong>" . $this->getLatestVersion() . "</strong></td></tr>
            </table><br /><br />
        ";

        if($this->isBehind(SYSTEM_VERSION)) {
            //The below is caught by the controller and will run the update function
            echo "<a href='?type=update' class='btn btn-primary'>Click here to update the system</a>";
         }  else {
			echo '<p> FerretCMS is up to date.</p>';
		 }
    }


    private function isBehind($currentVersion) {
        $ret = false;
		$latestVersion = $this->getLatestVersion();
        //Break the versions into major/minor/patch for checking
        $current = explode(".", $this->trimRelease($currentVersion));
        $latest = explode(".", $this->trimRelease($latestVersion));

        if($latest[0] > $current[0]) {
            //If the major version is behind
            $ret = true;
        } else if($latest[1] > $current[1]) {
            //If the minor version is behind
            $ret = true;
        } else if($latest[2] > $current[2]) {
            //If the patch version is behind
            $ret = true;
        } else if($this->getRelease($currentVersion) != $this->getRelease($latestVersion)) {
            //If the release is different. Ie current is -alpha and current is -beta
            $ret = true;
        }
		
        return $ret;
    }


    //Removes the release ie -alpha from the version if it exists
    private function trimRelease($version) {
        if(strpos($version, "-") > 0) {
            $version = substr($version, 0, strpos($version, "-"));
        }
        return $version;
    }

    //Returns the release ie -alpha from the version if it exsits
    private function getRelease($version) {
        $release = "";

        if(strpos($version, "-") > 0) {
            $release = trim(substr($version, strpos($version, "-"), strlen($version)));
        }

        return $release;
    }

    //Gets the latest version from GitHub
    private function getLatestVersion() {
        $ret = null;
        $ret = file_get_contents('http://raw.github.com/JRogaishio/ferretCMS/master/version.txt') or die ('ERROR GETTING LATEST VERSION FROM GITHUB');
        $ret = trim($ret);

        return $ret;

    }

    //Needs work
    public function update($user) {
		$ignore = array("ferretCMS-master", "config.php", ".htaccess");
		$branch = "ferretCMS-master";
		
        echo "<h1>SYSTEM UPDATE</h1>";

        ini_set('max_execution_time',60);
        //Check for an update. We have a simple file that has a new release version on each line. (1.00, 1.02, 1.03, etc.)
        $latestVersion = $this->getLatestVersion();
		
		
        if ($latestVersion != null) {
            //If we managed to access that file, then lets break up those release versions into an array.
            echo '<br /><p>CURRENT VERSION: '. SYSTEM_VERSION .'</p>';
            echo '<p>Reading Current Releases...</p>';			
			
			if ( $this->isBehind(SYSTEM_VERSION)) {
				echo '<strong>New Update Found: v' . $latestVersion . '</strong>';

				//Download The File If We Do Not Have It
				if ( !is_file( 'UPDATES/master.zip' )) {
					echo '<p>Downloading New Update</p>';
					$newUpdate = file_get_contents('https://github.com/JRogaishio/ferretCMS/archive/master.zip');
					if ( !is_dir( 'UPDATES/' ) ) mkdir ( 'UPDATES/' );
					$dlHandler = fopen('UPDATES/master.zip', 'w');
					if ( !fwrite($dlHandler, $newUpdate) ) { echo '<p>Could not save new update. Operation aborted.</p>'; exit(); }
					fclose($dlHandler);
					echo '<p>Update Downloaded And Saved</p>';
				} else {
					echo '<p>Update already downloaded.</p>';    
				}
				
				//Open The File And Do Stuff
				$zipHandle = zip_open('UPDATES/master.zip');
				echo "<strong>Update details:</strong><br />";
				echo '<div class="well cms_updateDetails"><ul>';
				while ($aF = zip_read($zipHandle) ) {
					$thisFileName = zip_entry_name($aF);
					$thisFileDir = dirname($thisFileName);
				   
					$thisFileName = str_replace($branch . "/", "", $thisFileName);
					
					//Trim out the origin branch folder
					if(strpos($thisFileDir, "ferretCMS-master") === 0) {
						$thisFileDir = str_replace($branch . "/", "", $thisFileDir);
						$thisFileDir = str_replace($branch, "", $thisFileDir);
					}
					
					//Continue if its not a file
					if ( substr($thisFileName,-1,1) == '/') continue;
				   
					//Skip over this item if it's in the ignore list
					if(in_array($thisFileName, $ignore) || (in_array($thisFileDir, $ignore))) {
						 echo '<li>'.str_pad($thisFileName, 65, ".", STR_PAD_RIGHT).' IGNORED</li>';
						continue;
					}
	
					//Make the directory if we need to...
					if ( !is_dir ( $thisFileDir ) && $thisFileDir != "") {
						 mkdir($thisFileDir, 0777, true);
						 
						 echo '<li>'.str_pad($thisFileDir, 65, ".", STR_PAD_RIGHT).' DIRECTORY CREATED</li>';
					}
				   
					//Overwrite the file
					if ( !is_dir($thisFileName) && $thisFileName != "" ) {
	
						
						echo '<li>'. str_pad($thisFileName, 65, ".", STR_PAD_RIGHT);
						$contents = zip_entry_read($aF, zip_entry_filesize($aF));
						//$contents = str_replace("\\r\\n", "\\n", $contents);
						$updateThis = '';
					   
						//If we need to run commands, then do it.
						if ( $thisFileName == 'upgrade.php' ) {
							$upgradeExec = fopen ('upgrade.php','w');
							fwrite($upgradeExec, $contents);
							fclose($upgradeExec);
							include ('upgrade.php');
							unlink('upgrade.php');
							echo' EXECUTED</li>';
						} else {
							$updateThis = fopen($thisFileName, 'w');
							fwrite($updateThis, $contents);
							fclose($updateThis);
							unset($contents);
							echo' UPDATED</li>';
						}
					}
				}
				zip_close($zipHandle);
				echo '</ul></div>';
				echo '<h4>>> FerretCMS Updated to v'.$latestVersion.'</h4>';
				$this->log->trackChange("updater", 'update',$user->getId(),$user->getLoginname(), "Updated to v$latestVersion");
				//Remove the updates directory now that we are done with it
				unlink("UPDATES/master.zip");
				rmdir('UPDATES/');
			}
            else {
				echo '<p>>> FerretCMS is up to date.</p>';
			}
        } else {
            echo '<p>Could not find latest realeases.</p>';
        }
		
    }
}

?>