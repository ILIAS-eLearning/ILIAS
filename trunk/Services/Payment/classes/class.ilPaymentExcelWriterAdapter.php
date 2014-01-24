<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPaymentExcelWriterAdapter
*
* @author Stefan Meyer <meyer@leifos.com> 
* @author Jens Conze <jc@databay.de> 
* @version $Id: class.ilPaymentExcelWriterAdapter.php 21503 2009-09-07 07:33:14Z hschottm $
* 
* @extends ilObjectGUI
* @package ilias-payment
*
*/

include_once './Services/Excel/classes/class.ilExcelWriterAdapter.php';
/*
 *  depricated?! 
 * */
class ilPaymentExcelWriterAdapter extends ilExcelWriterAdapter
{

	function ilPaymentExcelWriterAdapter($a_filename,$a_send = true)
	{
		parent::ilExcelWriterAdapter($a_filename,$a_send);
	}
}
?>
