<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomSmiliesGUI
 *
 * Chat smiley GUI handler
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesGUI
{

	/**
	 * Constructor
	 *
	 * @access public
	 */
	/*public function __construct()
	 {

	 }*/

	/**
	 * Default execute command, calls ilChatroomSmilies::initial();
	 */
	public function &executeCommand()
	{
		include_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
		ilChatroomSmilies::initial();
	}

	/**
	 * Instantiates ilChatroomSmiliesTableGUI and returns its table's HTML.
	 *
	 * @global ilLanguage $lng
	 * @global ilCtrl2 $ilCtrl
	 * @param ilObjChatroomAdminGUI $a_ref
	 * @return string
	 */
	public static function _getExistingSmiliesTable($a_ref)
	{
		global $lng, $ilCtrl;

		include_once "Modules/Chatroom/classes/class.ilChatroomSmiliesTableGUI.php";

		$table = new ilChatroomSmiliesTableGUI( $a_ref, 'smiley' );

		include_once('Modules/Chatroom/classes/class.ilChatroomSmilies.php');

		$values = ilChatroomSmilies::_getSmilies();
		$table->setData( $values );

		return $table->getHTML();
	}

}

?>
