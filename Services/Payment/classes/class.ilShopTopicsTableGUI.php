<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* 
* Class ilShopTopicsTableGUI
* 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$ 
* 
* @ingroup ServicesPayment
* 
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');


class ilShopTopicsTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = '')
	{
	 	global $lng, $ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj, $a_parent_cmd);
	 	$this->addColumn('', 'checkbox', '1%');
	 	$this->addColumn($this->lng->txt('title'), 'title' , '40%');
	 	$this->addColumn($this->lng->txt('date'), 'date' , '22%');
	 	$this->addColumn($this->lng->txt('pay_sorting_value'), 'sorting' , '22%');
	 	$this->addColumn('', 'edit', '15%');
	 		 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate('tpl.shop_topics_list_row.html', 'Services/Payment');
		$this->setDefaultOrderField('pt_topic_sort');
		$this->setDefaultOrderDirection('asc');
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{		
		$this->tpl->setVariable('VAL_ID', $a_set['id']);
		$this->tpl->setVariable('VAL_TITLE', $a_set['title']);
		$this->tpl->setVariable('VAL_DATE', ilFormat::formatDate(date('Y-m-d H:i:s', $a_set['date']), 'datetime', true));
		$this->tpl->setVariable('VAL_SORTING_TEXTINPUT', ilUtil::formInput('sorting['.$a_set['id'].']', $a_set['sorting']));

		$this->ctrl->setParameter($this->parent_obj, 'topic_id', $a_set['id']);
		$this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'showTopicForm'));
		$this->tpl->setVariable('TXT_EDIT_RECORD', $this->lng->txt('edit'));
	}
	
	/**
	 * Parse records
	 *
	 * @access public
	 * @param array array of record objects
	 * 
	 */
	public function parseRecords($a_topics)
	{
	 	foreach($a_topics as $topic)
	 	{
			$tmp_arr['id'] = $topic->getId();
			$tmp_arr['title'] = $topic->getTitle();
			$tmp_arr['date'] = $topic->getCreateDate();
			$tmp_arr['sorting'] = (int)$topic->getSorting();
			
			$records_arr[] = $tmp_arr;
	 	} 	
	 	
	 	if (!count($a_topics))
		{			
			$this->disable('header');
			$this->disable('footer');

			$this->setNoEntriesText($this->lng->txt('no_topics_yet'));
		}
		else
		{
			$this->addMultiCommand('confirmDeleteTopic', $this->lng->txt('delete'));
			$this->setSelectAllCheckbox('topic_id');
		}
	 	
	 	$this->setData($records_arr ? $records_arr : array());
	}
}
?>