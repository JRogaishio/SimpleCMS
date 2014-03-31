<?php

/**
 * Class to handle page-related requests
 *
 * @author Jacob Rogaishio
 * 
 */
class templateService extends service
{
	public function getTemplate() {
		return $this->model;
	}
}
