<?php

/**
 * Class to handle page-related requests
 *
 * @author Jacob Rogaishio
 * 
 */
class templateService extends service
{
	/**
	 * Get the template object that is tied to this service
	 *
	 * @return Returns the template object used by this service
	 */
	public function getTemplate() {
		return $this->model;
	}
}
