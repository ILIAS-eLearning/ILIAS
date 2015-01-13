<?php
require_once(dirname(__FILE__) . '/../../Connector/class.arConnectorDB.php');

/**
 * Class arBuilder
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arBuilder {

	/**
	 * @var ActiveRecord
	 */
	protected $ar;
	/**
	 * @var int
	 */
	protected $step;


	/**
	 * @param ActiveRecord $ar
	 * @param int          $step
	 */
	public function __construct(ActiveRecord $ar, $step = 1) {
		$this->setAr($ar);
		$this->setStep($step);
	}


	public function generateDBUpdateForInstallation() {
		$tpl = new ilTemplate(dirname(__FILE__) . '/templates/dbupdate.txt', true, true);
		$ar = $this->getAr();

		$tpl->setVariable('TABLE_NAME', $ar->getConnectorContainerName());
		$tpl->setVariable('TABLE_NAME2', $ar->getConnectorContainerName());
		$tpl->setVariable('TABLE_NAME3', $ar->getConnectorContainerName());
		$tpl->setVariable('STEP', $this->getStep());
		$tpl->setVariable('PRIMARY', $this->getAr()->getArFieldList()->getPrimaryFieldName());

		foreach ($this->getAr()->getArFieldList()->getFields() as $field) {
			$tpl->touchBlock('field');
			$tpl->setVariable('FIELD_NAME', $field->getName());
			foreach ($field->getAttributesForConnector() as $name => $value) {
				$tpl->setCurrentBlock('attribute');
				$tpl->setVariable('NAME', arFieldList::mapKey($name));
				$tpl->setVariable('VALUE', $value);
				$tpl->parseCurrentBlock();
			}
		}

		if ($this->getAr()->getArFieldList()->getPrimaryField()->getFieldType() == arField::FIELD_TYPE_INTEGER) {
			$tpl->setCurrentBlock('attribute');
			$tpl->setVariable('TABLE_NAME4', $ar->getConnectorContainerName());
			$tpl->parseCurrentBlock();
		}

		header('Content-type: application/x-httpd-php');
		header("Content-Disposition: attachment; filename=\"dbupdate.php\"");
		echo $tpl->get();
		exit;
	}


	/**
	 * @param \ActiveRecord $ar
	 */
	public function setAr($ar) {
		$this->ar = $ar;
	}


	/**
	 * @return \ActiveRecord
	 */
	public function getAr() {
		return $this->ar;
	}


	/**
	 * @param int $step
	 */
	public function setStep($step) {
		$this->step = $step;
	}


	/**
	 * @return int
	 */
	public function getStep() {
		return $this->step;
	}
}

?>
