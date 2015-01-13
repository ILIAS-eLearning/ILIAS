<?php
require_once(dirname(__FILE__) . '/../class.ActiveRecord.php');

/**
 * Class arStorage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
abstract class arStorage extends ActiveRecord {

	/**
	 * @var arTestRecord
	 */
	protected $external_model_for_storage;


	/**
	 * @param $model
	 *
	 * @return \arStorage
	 */
	public static function getInstance(&$model) {
		/**
		 * @var $storage arStorage
		 */

		arFieldCache::storeFromStorage(get_called_class(), $model);
		$storage = self::getCalledClass();
		$method = self::_toCamelCase('get_' . $storage->getArFieldList()->getPrimaryFieldName());
		$storage->setExternalModelForStorage($model);
		$storage->{$storage->getArFieldList()->getPrimaryFieldName()} = $model->{$method}();
		if ($storage->{$storage->getArFieldList()->getPrimaryFieldName()}) {
			$storage->read();
		}
		$storage->mapFromActiveRecord();

		return $storage;
	}


	public function create() {
		$this->mapToActiveRecord();
		parent::create();
	}


	public function update() {
		$this->mapToActiveRecord();
		parent::update();
	}


	public function read() {
		parent::read();
		$this->mapFromActiveRecord();
	}


	protected function mapToActiveRecord() {
		foreach (array_keys($this->getArFieldList()->getArrayForConnector()) as $key) {
			$this->{$key} = $this->getValueForStorage($key);
		}
	}


	protected function mapFromActiveRecord() {
		foreach (array_keys($this->getArFieldList()->getArrayForConnector()) as $key) {
			$this->setValueToModel($key, $this->{$key});
		}
	}


	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getValueForStorage($key) {
		$method = self::_toCamelCase('get_' . $key);

		return $this->getExternalModelForStorage()->{$method}();
	}


	/**
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setValueToModel($key, $value) {
		$method = self::_toCamelCase('set_' . $key);

		return $this->getExternalModelForStorage()->{$method}($value);
	}




	//
	// Setter & Getter
	//

	/**
	 * @param arTestRecord $model
	 */
	public function setExternalModelForStorage($model) {
		$this->external_model_for_storage = $model;
	}


	/**
	 * @return arTestRecord
	 */
	public function getExternalModelForStorage() {
		return $this->external_model_for_storage;
	}
}

?>

