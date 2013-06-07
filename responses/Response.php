<?php
namespace PhalconRest\Responses;

class Response extends \Phalcon\DI\Injectable{

	public function __construct($di){
		//parent::__construct();
		$this->setDI($di);
	}

}
