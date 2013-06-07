<?php
namespace PhalconRest\Responses;

class JSONResponse extends Response{

	protected $snake = true;
	protected $envelope = true;

	public function __construct($di){
		parent::__construct($di);
	}

	public function send($records){

		$response = $this->di->get('response');

		// If the query string 'envelope' is set to false, do not use the envelope.
		// Instead, return headers.
		$request = $this->di->get('request');
		if($request->get('envelope', null, null) === 'false'){
			$this->envelope = false;
		}

		// Most devs prefer camelCase to snake_Case in JSON, but this can be overriden here
		if($this->snake){
			$records = $this->arrayKeysToSnake($records);
		}

		if($this->envelope){
			// Provide an envelope for JSON responses.  '_meta' and 'records' are the objects. 
			$message = array();
			$message['_meta'] = array(
				'status' => 'SUCCESS',
				'count' => count($records)
			); 
			$message['records'] = $records;
		} else {
			$response->setHeader('X-Record-Count', count($records));
			$response->setHeader('X-Status', 'SUCCESS');
			$message = $records;
		}
		
		$response->setJsonContent($message);
		$response->send();

		return $this;
	}

	public function convertSnakeCase($snake){
		$this->snake = (bool) $snake;
		return $this;
	}

	public function useEnvelope($envelope){
		$this->envelope = (bool) $envelope;
		return $this;
	}

}
