<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBUpdate4550
 */
class ilDBUpdate4550
{
    public static function cleanupOrphanedChatRoomData()
    {
        /**
         * @var $ilDB ilDB
         */
        global $ilDB;

        // Delete orphaned rooms
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_settings c1
			INNER JOIN (
				SELECT chatroom_settings.room_id
				FROM chatroom_settings
				LEFT JOIN object_data
					ON object_data.obj_id = chatroom_settings.object_id
				WHERE object_data.obj_id IS NULL
			) c2
			ON c2.room_id = c1.room_id');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_settings
				WHERE chatroom_settings.room_id IN (
					SELECT chatroom_settings.room_id
					FROM chatroom_settings
					LEFT JOIN object_data
						ON object_data.obj_id = chatroom_settings.object_id
					WHERE object_data.obj_id IS NULL
				)'
            );
        }

        // Delete orphaned private rooms
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_prooms c1
			INNER JOIN (
				SELECT chatroom_prooms.proom_id
				FROM chatroom_prooms
				LEFT JOIN chatroom_settings
					ON chatroom_settings.room_id = chatroom_prooms.parent_id
				WHERE chatroom_settings.room_id IS NULL
			) c2
			ON c2.proom_id = c1.proom_id');
        } elseif ($ilDB->getDBType() == 'postgres') {
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_prooms
				WHERE chatroom_prooms.proom_id IN (
					SELECT c1.proom_id
					FROM chatroom_prooms c1
					LEFT JOIN chatroom_settings
						ON chatroom_settings.room_id = CAST(c1.parent_id as INT)
					WHERE chatroom_settings.room_id IS NULL
				)'
            );
        } else {
            // Oracle
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_prooms
				WHERE chatroom_prooms.proom_id IN (
					SELECT c1.proom_id
					FROM chatroom_prooms c1
					LEFT JOIN chatroom_settings
						ON chatroom_settings.room_id = c1.parent_id
					WHERE chatroom_settings.room_id IS NULL
				)'
            );
        }

        // Delete orphaned bans
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_bans c1
			INNER JOIN (
				SELECT chatroom_bans.room_id
				FROM chatroom_bans
				LEFT JOIN chatroom_settings
					ON chatroom_settings.room_id = chatroom_bans.room_id
				WHERE chatroom_settings.room_id IS NULL
			) c2
			ON c2.room_id = c1.room_id');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_bans
				WHERE chatroom_bans.room_id IN (
					SELECT chatroom_bans.room_id
					FROM chatroom_bans
					LEFT JOIN chatroom_settings
						ON chatroom_settings.room_id = chatroom_bans.room_id
					WHERE chatroom_settings.room_id IS NULL
				)'
            );
        }

        // Delete orphaned history entries
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_history c1
			INNER JOIN (
				SELECT chatroom_history.room_id
				FROM chatroom_history
				LEFT JOIN chatroom_settings
					ON chatroom_settings.room_id = chatroom_history.room_id
				WHERE chatroom_settings.room_id IS NULL
			) c2
			ON c2.room_id = c1.room_id');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_history
				WHERE chatroom_history.room_id IN (
					SELECT chatroom_history.room_id
					FROM chatroom_history
					LEFT JOIN chatroom_settings
						ON chatroom_settings.room_id = chatroom_history.room_id
					WHERE chatroom_settings.room_id IS NULL
				)'
            );
        }

        // Delete orphaned users
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_users c1
			INNER JOIN (
				SELECT chatroom_users.room_id
				FROM chatroom_users
				LEFT JOIN chatroom_settings
					ON chatroom_settings.room_id = chatroom_users.room_id
				WHERE chatroom_settings.room_id IS NULL
			) c2
			ON c2.room_id = c1.room_id');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_users
				WHERE chatroom_users.room_id IN (
					SELECT chatroom_users.room_id
					FROM chatroom_history
					LEFT JOIN chatroom_settings
						ON chatroom_settings.room_id = chatroom_users.room_id
					WHERE chatroom_settings.room_id IS NULL
				)'
            );
        }

        // Delete orphaned sessions
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_sessions c1
			INNER JOIN (
				SELECT chatroom_sessions.room_id
				FROM chatroom_sessions
				LEFT JOIN chatroom_settings
					ON chatroom_settings.room_id = chatroom_sessions.room_id
				WHERE chatroom_settings.room_id IS NULL
			) c2
			ON c2.room_id = c1.room_id');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_sessions
				WHERE chatroom_sessions.room_id IN (
					SELECT chatroom_sessions.room_id
					FROM chatroom_history
					LEFT JOIN chatroom_settings
						ON chatroom_settings.room_id = chatroom_sessions.room_id
					WHERE chatroom_settings.room_id IS NULL
				)'
            );
        }

        // Delete orphaned private sessions
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_psessions c1
			INNER JOIN (
				SELECT chatroom_psessions.proom_id
				FROM chatroom_psessions
				LEFT JOIN chatroom_prooms
					ON chatroom_prooms.proom_id = chatroom_psessions.proom_id
				WHERE chatroom_prooms.proom_id IS NULL
			) c2
			ON c2.proom_id = c1.proom_id');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_psessions
				WHERE chatroom_psessions.proom_id IN (
					SELECT chatroom_psessions.proom_id
					FROM chatroom_history
					LEFT JOIN chatroom_prooms
						ON chatroom_prooms.proom_id = chatroom_psessions.proom_id
					WHERE chatroom_prooms.proom_id IS NULL
				)'
            );
        }

        // Delete orphaned private access
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_proomaccess c1
			INNER JOIN (
				SELECT chatroom_proomaccess.proom_id
				FROM chatroom_proomaccess
				LEFT JOIN chatroom_prooms
					ON chatroom_prooms.proom_id = chatroom_proomaccess.proom_id
				WHERE chatroom_prooms.proom_id IS NULL
			) c2
			ON c2.proom_id = c1.proom_id');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_proomaccess
				WHERE chatroom_proomaccess.proom_id IN (
					SELECT chatroom_proomaccess.proom_id
					FROM chatroom_history
					LEFT JOIN chatroom_prooms
						ON chatroom_prooms.proom_id = chatroom_proomaccess.proom_id
					WHERE chatroom_prooms.proom_id IS NULL
				)'
            );
        }

        // Delete private room history
        if ($ilDB->getDBType() == '' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == 'innodb') {
            $ilDB->manipulate('
			DELETE c1
			FROM chatroom_history c1
			INNER JOIN (
				SELECT chatroom_history.sub_room
				FROM chatroom_history
				LEFT JOIN chatroom_prooms
					ON chatroom_prooms.proom_id = chatroom_history.sub_room
				WHERE chatroom_prooms.proom_id IS NULL AND chatroom_history.sub_room > 0
			) c2
			ON c2.sub_room = c1.sub_room');
        } else {
            // Oracle and Postgres
            $ilDB->manipulate(
                ' 
				DELETE FROM chatroom_history
				WHERE chatroom_history.sub_room IN (
					SELECT chatroom_history.sub_room
					FROM chatroom_history
					LEFT JOIN chatroom_prooms
						ON chatroom_prooms.proom_id = chatroom_history.sub_room
					WHERE chatroom_prooms.proom_id IS NULL AND chatroom_history.sub_room > 0
				)'
            );
        }
    }
}
