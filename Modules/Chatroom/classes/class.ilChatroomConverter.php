<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomConverter
 *
 * @author Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomConverter
{
	public function backupHistoryToXML()
	{
		global $ilDB;
			
		$res = $ilDB->query("
			SELECT		chat_id, room_id
			FROM		chat_room_messages
			GROUP BY	chat_id, room_id
		");

		$chat_room_id_comb = array();

		while( $row = $ilDB->fetchAssoc($res) )
		{
			$chat_room_id_comb[] = array( $row['chat_id'], $row['room_id'] );
		}

		foreach( $chat_room_id_comb as $combination )
		{
			$res = $ilDB->queryF("
				SELECT		*
				FROM		chat_room_messages
				WHERE		chat_id = %s
				AND			room_id = %s",
				
			array( 'integer', 'integer' ),
			array( $combination[0], $combination[1] )
			);
				
			$xml = new SimpleXMLElement('<entries />');
			$xml->addAttribute('chat_id', $combination[0]);
			$xml->addAttribute('room_id', $combination[1]);

			while( $row = $ilDB->fetchAssoc($res) )
			{
				$child = $xml->addChild('entry', $row['message']);
				$child->addAttribute('timestamp', $row['commit_timestamp']);
			}
				
			$xml->asXML();
		}
	}
}