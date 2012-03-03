<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjPaymentSettings
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjPaymentSettings extends ilObject
{
	public  $payment_vendors_obj = null;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function ilObjPaymentSettings($a_id = 0,$a_call_by_reference = true)
	{
		#define("ILIAS_MODULE","payment");
		#define("KEEP_IMAGE_PATH",1);

		$this->type = "pays";
		$this->ilObject($a_id,$a_call_by_reference);

		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('payment');
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	public function update()
	{
		if (!parent::update())
		{			
			return false;
		}

		// put here object specific stuff
		
		return true;
	}

	public function initPaymentVendorsObject()
	{
		if(!is_object($this->payment_vendors_obj))
		{
			include_once "./Services/Payment/classes/class.ilPaymentVendors.php";

			$this->payment_vendors_obj = new ilPaymentVendors();
		}
		return true;
	}
} // END class.ilObjPaymentSettings
?>
