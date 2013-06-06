<?php
namespace PhalconRest\Controllers;
use \PhalconRest\Exceptions\HTTPException;

class ExampleController extends RESTController{

	function get(){
		if($this->isSearch{
			$results = $this->search();
		}

		return $this->respond($results);
	}

	function post(){

	}

	function delete($id){

	}

	function search(){
		
	}

	function respond(){
		if($this->isPartial{

		}
		return;
	}

}