<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
* 
* 
* @ingroup ServicesPayment
*/

include_once './Services/Payment/classes/class.ilPaymentSettings.php';

class ilUserDefinedInvoiceNumber
{
	public $pSettings;

	public $ud_invoice_number = 0;
	public $invoice_number_text = null;
	public $inc_start_value = 0;
	public $inc_current_value = 0;
	public $inc_reset_period = 0;
	public $last_reset = 0;

	// SETTER/GETTER
	
	/* enable user-defined invoice number -> 1 
	 * disable for using standard ilias invoice number -> 0
	 *
	 */
	public function setUDInvoiceNumberActive($a_ud_invoice_number)
	{
		$this->ud_invoice_number = $a_ud_invoice_number;
	}
	public function getUDInvoiceNumberActive()
	{
		return $this->ud_invoice_number;
	}

	public function setInvoiceNumberText($a_invoice_number_text)
	{
		$this->invoice_number_text = $a_invoice_number_text;
	}
	public function getInvoiceNumberText()
	{
		return $this->invoice_number_text;
	}

	public function setIncStartValue($a_inc_start_value)
	{
		$this->inc_start_value = $a_inc_start_value;
	}
	public function getIncStartValue()
	{
		return $this->inc_start_value;
	}

	public function setIncCurrentValue($a_inc_current_value)
	{
		$this->inc_current_value = $a_inc_current_value;
	}
	public function getIncCurrentValue()
	{
		return $this->inc_current_value;
	}

	/*
	 *  @param integer $a_inc_reset_period (1=yearly, 2=monthly)
	 */
	public function setIncResetPeriod($a_inc_reset_period)
	{
		$this->inc_reset_period = $a_inc_reset_period;
	}
	public function getIncResetPeriod()
	{
		return $this->inc_reset_period;
	}

	/*
	 * @param integer timestamp
	 */
	public function setIncLastReset($a_timestamp)
	{
		$this->inc_last_reset = $a_timestamp;
	}

	public function getIncLastReset()
	{
		return $this->inc_last_reset;
	}

	public function __construct()
	{
		$this->pSettings = ilPaymentSettings::_getInstance();
		$this->read();
	}

	public function read()
	{
		$settings = $this->pSettings->getValuesByScope('invoice_number');
		$this->ud_invoice_number = $settings['ud_invoice_number'];
		$this->invoice_number_text = $settings['invoice_number_text'];
		$this->inc_start_value = $settings['inc_start_value'];
		$this->inc_current_value = $settings['inc_current_value'];
		$this->inc_reset_period = $settings['inc_reset_period'];
		$this->inc_last_reset = $settings['inc_last_reset'];
	}

	public function update()
	{
		$this->pSettings->set('ud_invoice_number', $this->getUDInvoiceNumberActive(),'invoice_number');
		$this->pSettings->set('invoice_number_text', $this->getInvoiceNumberText(),'invoice_number');
		$this->pSettings->set('inc_start_value', $this->getIncStartValue(),'invoice_number');
		$this->pSettings->set('inc_reset_period', $this->getIncResetPeriod(),'invoice_number');
	}


	public static function _nextIncCurrentValue()
	{
		$pSettings = ilPaymentSettings::_getInstance();
		$cur_id = $pSettings->get('inc_current_value');
		$next_id = ++$cur_id;

		$pSettings->set('inc_current_value', $next_id, 'invoice_number');

		return $next_id;

	}
	/**
	 * @param  integer $a_value
	 */
	public static function _setIncCurrentValue($a_value)
	{
		$pSettings = ilPaymentSettings::_getInstance();
		$pSettings->set('inc_current_value', $a_value, 'invoice_number');
	}

	public static function _getIncCurrentValue()
	{
		$pSettings = ilPaymentSettings::_getInstance();
		return $pSettings->get('inc_current_value');

	}
	public static function _getResetPeriod()
	{
		$pSettings = ilPaymentSettings::_getInstance();
		return $pSettings->get('inc_reset_period');
	}

	/*
	 *
	 *  @return boolean
	 */
	public static function _isUDInvoiceNumberActive()
	{
		$pSettings = ilPaymentSettings::_getInstance();

		if(!IS_PAYMENT_ENABLED) return false;

		if($pSettings->get('ud_invoice_number') == 1)
			return true;
		else
			return false;
	}

// CRON CHECK
	public function cronCheck()
	{
		$last_reset = $this->getIncLastReset();
		$last_month = date('n', $last_reset);
		$last_year = date('Y', $last_reset);

		$now = time();
		$now_month = date('n', $now);
		$now_year = date('Y', $now);

		$reset_type = $this->getIncResetPeriod();

		switch($reset_type)
		{
			case '1': #yearly
				if($last_year < $now_year)
				{
					$reset = true;
				}
				break;
			case '2': #monthly
				if(($last_month < $now_month) || ($last_month > $now_month && $last_year < $now_year) )
				{
					$reset = true;
				}
				break;
			default:
				$reset = false;
				break;
		}

		if($reset == true)
		{
			$this->setIncCurrentValue($this->getIncStartValue());
			$this->setIncLastReset(mktime(0, 0, 0, $now_month, 1, $now_year));
			$this->__updateCron();
		}
	}

	private function __updateCron()
	{
		$this->pSettings->set('inc_current_value',$this->getIncCurrentValue(),'invoice_number');
		$this->pSettings->set('inc_last_reset',$this->getIncLastReset(), 'invoice_number');
	}
}
?>