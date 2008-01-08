<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilObjPaymentSettings
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "./classes/class.ilObject.php";

class ilObjPaymentSettings extends ilObject
{
	var $payment_vendors_obj = null;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjPaymentSettings($a_id = 0,$a_call_by_reference = true)
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
	function update()
	{
		if (!parent::update())
		{			
			return false;
		}

		// put here object specific stuff
		
		return true;
	}

	function initPaymentVendorsObject()
	{
		if(!is_object($this->payment_vendors_obj))
		{
			include_once "./payment/classes/class.ilPaymentVendors.php";

			$this->payment_vendors_obj =& new ilPaymentVendors();
		}
		return true;
	}
} // END class.ilObjPaymentSettings
?>
