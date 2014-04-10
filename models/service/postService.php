<?php

/**
 * Class to handle page-related requests
 *
 * @author Jacob Rogaishio
 * 
 */
class postService extends service
{
	/**
	 * Get the post object that is tied to this service
	 *
	 * @return Returns the post object used by this service
	 */
	public function getPost() {
		return $this->model;
	}
}
