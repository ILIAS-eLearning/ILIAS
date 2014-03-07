<?php


require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');

/**
 * Class arStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
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
		 * @var $obj arStorage
		 */
		$class = get_called_class();
		self::setDBFields($model, $class);
		$method = self::_toCamelCase('get_' . self::returnPrimaryFieldName());
		$obj = new $class();
		$obj->setExternalModelForStorage($model);
		$obj->{self::returnPrimaryFieldName()} = $model->{$method}();
		$obj->read();
		$obj->mapFromActiveRecord();

		return $obj;
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
		foreach (array_keys(self::returnDbFields()) as $key) {
			$this->{$key} = $this->getValueForStorage($key);
		}
	}


	protected function mapFromActiveRecord() {
		foreach (array_keys(self::returnDbFields()) as $key) {
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

