<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomSmiliesCurrentSmileyFormElement
 *
 * Class ilchatroomSmiliesCurrentSmileyFormElement
 * simple form element that displays an image; does not add data to the containing form
 * but may be initialized by default methods, such as valuesByArray
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesCurrentSmileyFormElement extends ilCustomInputGUI
{

	/**
	 * Returns template HTML.
	 *
	 * @global ilLanguage $lng
	 * @return string
	 */
	public function getHtml()
	{
		global $lng;

		$tpl = new ilTemplate( "tpl.chatroom_current_smiley_image.html", true, true, "Modules/Chatroom" );
		$tpl->setVariable( "IMAGE_ALT", $lng->txt( "chatroom_current_smiley_image" ) );
		$tpl->setVariable( "IMAGE_PATH", $this->value );

		return $tpl->get();
	}

	/**
	 * Returns $this->value of ilChatroomSmiliesCurrentSmileyFormElement
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets given value as $this->value in
	 * ilChatroomSmiliesCurrentSmileyFormElement
	 *
	 * @param string $a_value
	 */
	public function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	 * Set value by array
	 *
	 * @param	array	$a_values	value array
	 */
	/*function setValueByArray($a_values)
	 {
		$this->setValue( $a_values[$this->getPostVar()] );
		}*/

	/**
	 * Check Input
	 *
	 * @return boolean
	 */
	public function checkInput()
	{
		return true;
	}

}