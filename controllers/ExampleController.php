<?php
namespace PhalconRest\Controllers;
use \PhalconRest\Exceptions\HTTPException;

class ExampleController extends RESTController{

	function get(){
		if($this->isSearch){
			$results = $this->search();
		} else {
			$results = array('Get / stub');
		}

		return $this->respond($results);
	}

	function post(){
		return array('Post / stub');
	}

	function delete($id){
		return array('Delete / stub');
	}

	function search(){
		return array('search' => $this->searchFields);
	}

	function respond($results){
		if($this->isPartial){
			$results['partials'] = $this->partialFields;
		}
		if($this->limit){
			$results['limit'] = $this->limit;
		}
		if($this->offset){
			$results['offset'] = $this->offset;
		}
		return $results;
	}

}