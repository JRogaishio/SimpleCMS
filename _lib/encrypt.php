<?php

function unique_salt() {
    mt_srand(microtime(true)*100000 + memory_get_usage(true));
    return hash('sha256', (uniqid(mt_rand(), true)));
}

function encrypt($pass, $userSalt) {
	$ret = null;
	
	$ret = hash('sha256',$pass);
	$ret = hash('sha256',($ret . $userSalt));
	
	return $ret;
}
?>