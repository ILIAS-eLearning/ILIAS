<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilXmlWriter.php';

/**
 * Class ilChatroomXMLWriter
 */
class lChatroomXMLParser extends ilXmlWriter
{
	/**
	 * @var ilObjChatroom
	 */
	protected $chat;

	/**
	 * ilChatroomXMLWriter constructor.
	 * @param ilObjChatroom $chat
	 */
	public function __construct(ilObjChatroom $chat)
	{
		$this->chat = $chat;

		parent::__construct();
	}

	/**
	 * 
	 */
	public function start()
	{
		$this->xmlStartTag('Chatroom', null);

		$this->xmlElement('ObjId', null, $this->chat->getId());
		$this->xmlElement('Title',  null, $this->chat->getTitle());
		$this->xmlElement('Description',  null, $this->chat->getDescription());

		$this->xmlEndTag('Chatroom');
	}

	/**
	 * @return string
	 */
	public function getXML()
	{
		// Replace ascii code 11 characters because of problems with xml sax parser
		return str_replace('&#11;', '', $this->xmlDumpMem(false));
	}
}