<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilSaxParser.php';

/**
 * Class ilChatroomXMLParser
 */
class ilChatroomXMLParser extends ilSaxParser
{
	/**
	 * @var ilObjChatroom
	 */
	protected $chat;

	/**
	 * Constructor
	 *
	 * @param ilObjChatroom $chat
	 * @param string $a_xml_data
	 */
	public function __construct($chat, $a_xml_data)
	{
		parent::__construct();

		$this->chat = $chat;
		$this->setXMLContent('<?xml version="1.0" encoding="utf-8"?>'.$a_xml_data);
	}
}