<?php
namespace PhalconRest\Responses;

class JSONResponse extends Response{

	protected $snake = true;
	protected $envelope = true;

	public function __construct(){
		parent::__construct();
	}

	public function send($records, $error=false){

		// Error's come from HTTPException.  This helps set the proper envelope data
		$response = $this->di->get('response');
		$success = ($error) ? 'ERROR' : 'SUCCESS';

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

		$etag = md5(serialize($records));

		if($this->envelope){
			// Provide an envelope for JSON responses.  '_meta' and 'records' are the objects.
			$message = array();
			$message['_meta'] = array(
				'status' => $success,
				'count' => ($error) ? 1 : count($records)
			);

			// Handle 0 record responses, or assign the records
			if($message['_meta']['count'] === 0){
				// This is required to make the response JSON return an empty JS object.  Without
				// this, the JSON return an empty array:  [] instead of {}
				$message['records'] = new \stdClass();
			} else {
				$message['records'] = $records;
			}

		} else {
			$response->setHeader('X-Record-Count', count($records));
			$response->setHeader('X-Status', $success);
			$message = $records;
		}

		$response->setContentType('application/json');
		$response->setHeader('E-Tag', $etag);

		// HEAD requests are detected in the parent constructor. HEAD does everything exactly the
		// same as GET, but contains no body.
		if(!$this->head){
			$response->setJsonContent($message);
		}

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
