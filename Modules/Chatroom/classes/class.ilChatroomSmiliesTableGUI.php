<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

/**
 * Class ilChatroomSmiliesTableGUI
 *
 * Prepares table rows and fills them.
 *
 * @author Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesTableGUI extends ilTable2GUI
{
	private $gui = null;

	/**
	 * Constructor
	 *
	 * Prepares smilies table.
	 *
	 * @global ilLanguage $lng
	 * @global ilCtrl2 $ilCtrl
	 * @param ilObjChatroomAdminGUI $a_ref
	 * @param string $cmd
	 */
	public function __construct($a_ref, $cmd)
	{
		global $lng, $ilCtrl;

		parent::__construct( $a_ref, $cmd );

		$this->gui = $a_ref;

		$this->setTitle( $lng->txt( 'chatroom_available_smilies' ) );
		$this->setId( 'chatroom_smilies_tbl' );

		$this->addColumn( '', 'checkbox', '2%', true );
		$this->addColumn( $lng->txt( 'chatroom_smiley_image' ), '', '28%' );
		$this->addColumn( $lng->txt( 'chatroom_smiley_keyword' ), 'keyword', '55%' );
		$this->addColumn( $lng->txt( 'actions' ), '', '15%' );

		$this->setFormAction( $ilCtrl->getFormAction( $a_ref ) );
		$this->setRowTemplate( 'tpl.chatroom_smiley_list_row.html', 'Modules/Chatroom' );
		$this->setSelectAllCheckbox( 'smiley_id' );

		$this->addMultiCommand(
			"smiley-deleteMultipleObject", $lng->txt( "chatroom_delete_selected" )
		);
	}

	/**
	 * Fills table rows with content from $a_set.
	 *
	 * @global ilCtrl2 $ilCtrl
	 * @param array $a_set
	 */
	public function fillRow($a_set)
	{
		global $ilCtrl;

		$this->tpl->setVariable( 'VAL_SMILEY_ID', $a_set['smiley_id'] );
		$this->tpl->setVariable( 'VAL_SMILEY_PATH', $a_set['smiley_fullpath'] );
		$this->tpl->setVariable( 'VAL_SMILEY_KEYWORDS', $a_set['smiley_keywords'] );
		$this->tpl->setVariable(
			'VAL_SMILEY_KEYWORDS_NONL',
		str_replace( "\n", "", $a_set['smiley_keywords'] )
		);
		$this->tpl->setVariable(
			'VAL_SORTING_TEXTINPUT',
		ilUtil::formInput( 'sorting[' . $a_set['id'] . ']',
		$a_set['sorting'] )
		);

		$ilCtrl->setParameter( $this->gui, 'topic_id', $a_set['id'] );

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle( $this->lng->txt( "actions" ) );
		$current_selection_list->setId( "act_" . $a_set['smiley_id'] );

		$current_selection_list->addItem(
		$this->lng->txt( "edit" ),
			'',
		$ilCtrl->getLinkTarget( $this->gui, 'smiley-showEditSmileyEntryFormObject' ) .
			"&smiley_id=" . $a_set['smiley_id']
		);
		$current_selection_list->addItem(
		$this->lng->txt( "delete" ),
			'',
		$ilCtrl->getLinkTarget( $this->gui, 'smiley-showDeleteSmileyFormObject' ) .
			"&smiley_id=" . $a_set['smiley_id']
		);

		$this->tpl->setVariable( 'VAL_ACTIONS', $current_selection_list->getHTML() );
	}

}
