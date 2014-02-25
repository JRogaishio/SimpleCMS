<?php

/**
 * Generates a random salt using SHA256, a random number using the mt_srand, microtime. the current memory usage and the uniqid function
 * 
 * @return returns a random salt
 */
function unique_salt() {
    mt_srand(microtime(true)*100000 + memory_get_usage(true));
    return hash('sha256', (uniqid(mt_rand(), true)));
}

/**
 * Encrypts a users password by using SHA256 and applying a salt
 * 
 * @param $pass		The users unencrypted password
 * @param $userSalt	The users unique salt
 * 
 * @return returns an encrypted password
 */
function encrypt($pass, $userSalt) {
	$ret = null;
	
	$ret = hash('sha256',$pass);
	$ret = hash('sha256',($ret . $userSalt));
	
	return $ret;
}
?>

