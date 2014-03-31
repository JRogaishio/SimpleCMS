<?php

/**
 * Class to handle page-related requests
 *
 * @author Jacob Rogaishio
 * 
 */
class postService extends service
{
	public function getPost() {
		return $this->model;
	}
}
