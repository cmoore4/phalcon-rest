<?php
namespace PhalconRest\Models;
use \PhalconRest\Exceptions\HTTPException;

class Princesses extends \Phalcon\Mvc\Model{

	/**
	 * These set the values for soft deletion in the database (using a deleted 
	 * flag instead of removing the entry)
	 */
	const DELETED = 1;
	const NOT_DELETED = 0;

	/**
	 * Returns the name of the table to use in the database
	 * @return string
	 */
	public function getSource(){
		return "princesses";
	}

	/**
	 * Sets up behaviors for this model.  This is run when a model is instantiated.
	 * @return void
	 */
	public function initialize(){

		/**
		 * Behaviors change the way the ORM interfaces with the Database.  SoftDelete
		 * causes the deleted flag to be set when an object is "deleted" instead of
		 * removing the row from the database.  This does not effect selects, which still
		 * require you to code in this condition.
		 */
		$this->addBehavior(
			new \Phalcon\Mvc\Model\Behavior\SoftDelete(array(
				'field' => 'deleted',
				'value' => Princesses::DELETED
			)
		));

		$this->addBehavior(
			new \Phalcon\Mvc\Model\Behavior\Timestampable(array(
				'beforeCreate' => array(
					'field' => 'last_edit',
					'format' => 'Y-m-d H:i:s'
				),
				'beforeUpdate' => array(
					'field' => 'last_edit',
					'format' => 'Y-m-d H:i:s'
				)
			)
		));

		return;
	}

	/**
	 * Validates a model before submitting it for creation or deletion.  Our Princess model
	 * must not be born before now, as we don't support future princesses.
	 * @return bool
	 */
	public function validation(){

		if(date_create_from_format('U', strtotime($this->birth_date)) > new DateTime()){
			throw new HTTPException(
				$this->appendMessage(
					'Princesses born in the future are currently unsupported.  You said your princess was born on ' . $this->birth_date,
					'birth_date',
					'InvalidValue'
				)
			);
			
			return false;
		}

		return true;
	}

}