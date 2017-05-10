<?php
include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Class ilSoapHookPlugin
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class ilSoapHookPlugin extends ilPlugin {

	/**
	 * @inheritdoc
	 */
	function getComponentType() {
		return IL_COMP_SERVICE;
	}

	/**
	 * @inheritdoc
	 */
	function getComponentName() {
		return 'WebServices';
	}

	/**
	 * @inheritdoc
	 */
	function getSlot() {
		return 'SoapHook';
	}

	/**
	 * @inheritdoc
	 */
	function getSlotId() {
		return 'soaphk';
	}

	/**
	 * Get all soap methods which will be made available to the SOAP webservice
	 *
	 * @return ilSoapMethod[]
	 */
	abstract public function getSoapMethods();

	/**
	 * Get any (new) types which the SOAP methods may use.
	 * These types are registered in WSDL.
	 *
	 * @see ilNusoapUserAdministrationAdapter::__registerMethods()
	 *
	 * @return ilWsdlType[]
	 */
	abstract public function getWsdlTypes();

	/**
	 * @inheritdoc
	 */
	protected function slotInit() {
	}

}