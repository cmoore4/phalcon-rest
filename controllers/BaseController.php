<?php
namespace PhalconRest\Controllers;

/**
 *  \Phalcon\Mvc\Controller has a final __construct() method, so we can't
 *  extend the constructor (which we will need for our RESTController).
 *  Thus we extend DI\Injectable instead.
 */
class BaseController extends \Phalcon\DI\Injectable{

	public function __construct(){
		//parent::__construct();
		$di = \Phalcon\DI::getDefault();
		$this->setDI($di);
	}

}