<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Payment/classes/class.ilShopTableGUI.php';

/**
 *
 * Class ilShopCouponsTableGUI
 *
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 * @version $Id:$
 *
 * @ingroup ServicesPayment
 *
 */
class ilShopCouponsTableGUI extends ilShopTableGUI
{
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 *
	 */
	public function fillRow($a_set)
	{
		foreach($a_set as $field => $value)
		{
			$content = self::formatField($field, $value);
			$this->tpl->setVariable('VAL_' . strtoupper($field), $content);
		}
	}

	/**
	 * @param $field string
	 * @param $value string
	 * @return string
	 * @static
	 */
	protected function formatField($field, $value)
	{
		switch($field)
		{
			case 'pc_from':
			case 'pc_till':
				return self::formatDateField($value);

			case 'pc_last_changed':
				return self::formatDateTimeField($value);

			default:
				return $value;
		}
	}

	/**
	 * @static
	 * @param $value string
	 * @return string
	 */
	protected static function formatDateField($value)
	{
		if(!strlen($value))
			return $value;

		return ilDatePresentation::formatDate(new ilDate($value, IL_CAL_DATE));
	}

	/**
	 * @static
	 * @param $value string
	 * @return string
	 */
	protected static function formatDateTimeField($value)
	{
		if(!strlen($value))
			return $value;

		return ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_DATETIME));
	}
}