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
* Class ilPaymentStatisticGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/
include_once './payment/classes/class.ilPaymentObject.php';

class ilPaymentStatisticGUI extends ilPaymentBaseGUI
{
	var $ctrl;

	var $lng;
	var $user_obj;
	var $pobject = null;

	function ilPaymentStatisticGUI(&$user_obj)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;

		$this->ilPaymentBaseGUI();

		$this->user_obj =& $user_obj;

		$this->pobject =& new ilPaymentObject($this->user_obj);

	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree;

		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{

			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showStatistics';
				}
				$this->$cmd();
				break;
		}
	}

	function showStatistics()
	{
		$this->__initBookingObject();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_statistic.html',true);
		
		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			sendInfo($this->lng->txt('paya_no_bookings'));

			return true;
		}


		$img_change = "<img src=\"".ilUtil::getImagePath("edit.gif")."\" alt=\"".
			$this->lng->txt("edit")."\" title=\"".$this->lng->txt("edit").
			"\" border=\"0\" vspace=\"0\"/>";
		
		$counter = 0;
		foreach($bookings as $booking)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($booking['ref_id']);
			$tmp_vendor =& ilObjectFactory::getInstanceByObjId($booking['b_vendor_id']);
			$tmp_purchaser =& ilObjectFactory::getInstanceByObjId($booking['customer_id']);
			
			$f_result[$counter][] = $booking['transaction'];
			$f_result[$counter][] = $tmp_obj->getTitle();
			$f_result[$counter][] = '['.$tmp_vendor->getLogin().']';
			$f_result[$counter][] = '['.$tmp_purchaser->getLogin().']';
			$f_result[$counter][] = date('Y m d H:i:s',$booking['order_date']);
			$f_result[$counter][] = $booking['duration'];
			$f_result[$counter][] = $booking['price'];

			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$f_result[$counter][] = $payed_access;

			$this->ctrl->setParameter($this,"booking_id",$booking['booking_id']);
			$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\"> ".
				$img_change."</a>";

			$f_result[$counter][] = $link_change;

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return $this->__showStatisticTable($f_result);

	}
	function editStatistic($a_show_confirm_delete = false)
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->showButton('showStatistics',$this->lng->txt('back'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_edit_statistic.html',true);
		$this->ctrl->setParameter($this,'booking_id',(int) $_GET['booking_id']);

		// confirm delete
		if($a_show_confirm_delete)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDelete');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('confirm'));
			$this->tpl->parseCurrentBlock();
		}
			

		$this->__initBookingObject();
		$bookings = $this->booking_obj->getBookings();
		$booking = $bookings[(int) $_GET['booking_id']];

		// get customer_obj
		$tmp_user =& ilObjectFactory::getInstanceByObjId($booking['customer_id']);



		$this->tpl->setVariable("STAT_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_usr_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("TITLE",$tmp_user->getFullname());
		$this->tpl->setVariable("DESCRIPTION",$tmp_user->getLogin());

		// TXT
		$this->tpl->setVariable("TXT_TRANSACTION",$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable("TXT_PAY_METHOD",$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable("TXT_ORDER_DATE",$this->lng->txt('paya_order_date'));
		$this->tpl->setVariable("TXT_DURATION",$this->lng->txt('duration'));
		$this->tpl->setVariable("TXT_PRICE",$this->lng->txt('price_a'));
		$this->tpl->setVariable("TXT_PAYED",$this->lng->txt('paya_payed'));
		$this->tpl->setVariable("TXT_ACCESS",$this->lng->txt('paya_access'));

		$this->tpl->setVariable("TRANSACTION",$booking['transaction']);

		$tmp_vendor =& ilObjectFactory::getInstanceByObjId($booking['b_vendor_id']);

		$this->tpl->setVariable("VENDOR",$tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');

		switch($booking['b_pay_method'])
		{
			case $this->pobject->PAY_METHOD_BILL:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('pays_bill'));
				break;

			case $this->pobject->PAY_METHOD_BMF:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('pays_bmf'));
				break;

			default:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('paya_pay_method_not_specified'));
				break;
		}
		$this->tpl->setVariable("ORDER_DATE",date('Y m d H:i:s',$booking['order_date']));
		$this->tpl->setVariable("DURATION",$booking['duration'].' '.$this->lng->txt('paya_months'));
		$this->tpl->setVariable("PRICE",$booking['price']);
		
		$yes_no = array(0 => $this->lng->txt('no'),1 => $this->lng->txt('yes'));

		$this->tpl->setVariable("PAYED",ilUtil::formSelect((int) $booking['payed'],'payed',$yes_no,false,true));
		$this->tpl->setVariable("ACCESS",ilUtil::formSelect((int) $booking['access'],'access',$yes_no,false,true));

		// buttons
		$this->tpl->setVariable("INPUT_CMD",'updateStatistic');
		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		$this->tpl->setVariable("DELETE_CMD",'deleteStatistic');
		$this->tpl->setVariable("DELETE_VALUE",$this->lng->txt('delete'));
	}

	function updateStatistic()
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}
		$this->__initBookingObject();

		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		
		if($this->booking_obj->update())
		{
			sendInfo($this->lng->txt('paya_updated_booking'));

			$this->showStatistics();
			return true;
		}
		else
		{
			sendInfo($this->lng->txt('paya_error_update_booking'));
			$this->showStatistics();
			
			return true;
		}
	}

	function deleteStatistic()
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}
		sendInfo($this->lng->txt('paya_sure_delete_stat'));

		$this->editStatistic(true);

		return true;
	}

	function performDelete()
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->__initBookingObject();
		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		if(!$this->booking_obj->delete())
		{
			die('Error deleting booking');
		}
		sendInfo($this->lng->txt('pay_deleted_booking'));

		$this->showStatistics();

		return true;
	}

	// PRIVATE
	function __showStatisticTable($a_result_set)
	{
		$tbl =& $this->initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		/*
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",6);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME","deleteTrustee");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();
		*/

		$tbl->setTitle($this->lng->txt("paya_statistic"),"icon_pays_b.gif",$this->lng->txt("paya_statistic"));
		$tbl->setHeaderNames(array($this->lng->txt("paya_transaction"),
								   $this->lng->txt("title"),
								   $this->lng->txt("paya_vendor"),
								   $this->lng->txt("paya_customer"),
								   $this->lng->txt("paya_order_date"),
								   $this->lng->txt("duration"),
								   $this->lng->txt("price_a"),
								   $this->lng->txt("paya_payed_access"),
								   $this->lng->txt("edit")));

		$tbl->setHeaderVars(array("transaction",
								  "title",
								  "vendor",
								  "customer",
								  "order_date",
								  "duration",
								  "price",
								  "payed_access",
								  "options"),
							array("cmd" => "",
								  "cmdClass" => "ilpaymentstatisticgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"] ? $_GET['sort_order'] : 'desc';

		$tbl->setOrderColumn($order,'order_date');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($a_result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($a_result_set);


		$tbl->render();

		$this->tpl->setVariable("STATISTIC_TABLE",$tbl->tpl->get());

		return true;
	}

	function __initBookingObject()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		$this->booking_obj =& new ilPaymentBookings($this->user_obj->getId());
	}

	function __showButton($a_cmd,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,$a_cmd));
		$this->tpl->setVariable("BTN_TXT",$a_text);
		if($a_target)
		{
			$this->tpl->setVariable("BTN_TARGET",$a_target);
		}

		$this->tpl->parseCurrentBlock();
	}		

}
?>