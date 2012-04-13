<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Mail query class.
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 * 
 */
class ilMailBoxQuery
{	
	public static $folderId = -1;
	public static $userId = -1;
	public static $limit = 0;
	public static $offset = 0;
	public static $orderDirection = '';
	public static $orderColumn = '';

	/**
	 * _getMailBoxListData
	 *
	 * @access	public
	 * @static
	 * @return	array	Array of mails
	 * 
	 */
	public static function _getMailBoxListData()
	{
		global $ilDB;
		
		// initialize array
		$mails = array('cnt' => 0, 'cnt_unread' => 0, 'set' => array());
		
		// count query
		$queryCount = 'SELECT COUNT(mail_id) cnt FROM mail '
			   	    . 'LEFT JOIN usr_data ON usr_id = sender_id '
			   		. 'WHERE user_id = %s '
					. 'AND ((sender_id > 0 AND sender_id IS NOT NULL AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
			   		. 'AND folder_id = %s ' 
					. 'UNION ALL '
					. 'SELECT COUNT(mail_id) cnt FROM mail '
					. 'LEFT JOIN usr_data ON usr_id = sender_id '
			   		. 'WHERE user_id = %s '
					. 'AND ((sender_id > 0 AND sender_id IS NOT NULL AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
			   		. 'AND folder_id = %s '
					. 'AND m_status = %s';   
		
		$res = $ilDB->queryf(
			$queryCount,
			array('integer', 'integer', 'integer', 'integer', 'text'),
			array(self::$userId, self::$folderId, self::$userId, self::$folderId, 'unread')
		);
		$counter = 0;
		while($cnt_row = $ilDB->fetchAssoc($res))
		{
			if($counter == 0)
			{
				$mails['cnt'] = $cnt_row['cnt'];
			}
			else if($counter == 1)
			{
				$mails['cnt_unread'] = $cnt_row['cnt'];
			}
			else
			{
				break;
			}
			
			++$counter;
		}	
		
		$sortColumn = '';
		if(self::$orderColumn == 'rcp_to' && $ilDB->getDBType() == 'oracle')
		{
			$sortColumn = ", CAST(rcp_to AS VARCHAR2(4000)) SORTCOL";
		}

		if(self::$orderColumn == 'from')
		{
			// Because of the user id of automatically generated mails and ordering issues we have to do some magic
			$firstname = '
				,(CASE
					WHEN (usr_id = '.ANONYMOUS_USER_ID.') THEN firstname 
					ELSE '.$ilDB->quote(ilMail::_getIliasMailerName(), 'text').'
				END) fname
			';
		}
		
		// item query
		$query = 'SELECT mail.*'.$sortColumn.' '.$firstname.' FROM mail '
			   . 'LEFT JOIN usr_data ON usr_id = sender_id '
			   . 'AND ((sender_id > 0 AND sender_id IS NOT NULL AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
			   . 'WHERE user_id = %s '
			   . 'AND folder_id = %s';	   
		
		// order direction
		$orderDirection = '';			
		if(in_array(strtolower(self::$orderDirection), array('desc', 'asc')))
		{
			$orderDirection = self::$orderDirection;	
		}
		else
		{
			$orderDirection = 'ASC';	
		}			   
			  
		// order column
		if(self::$orderColumn == 'rcp_to' && $ilDB->getDBType() == 'oracle')
		{
			$query .= ' ORDER BY SORTCOL '.$orderDirection;
		}
		else if(self::$orderColumn == 'from')
		{
			$query .= ' ORDER BY '
				    . ' fname '.$orderDirection.', '
				    . ' lastname '.$orderDirection.', '
				    . ' login '.$orderDirection.', '
				    . ' import_name '.$orderDirection;
		}
		else if(strlen(self::$orderColumn))
		{
			if(!in_array(strtolower(self::$orderColumn), array('m_subject', 'send_time', 'rcp_to')) &&
			   !$ilDB->tableColumnExists('mail', strtolower(self::$orderColumn)))
			{
				// @todo: Performance problem...
				self::$orderColumn = 'send_time';
			}
			
			$query .= ' ORDER BY '.strtolower(self::$orderColumn).' '.$orderDirection;
		}
		else
		{
			$query .= ' ORDER BY send_time DESC';
		}
		
		$ilDB->setLimit(self::$limit, self::$offset);
		$res = $ilDB->queryF(
			$query,
			array('integer', 'integer'),
			array(self::$userId, self::$folderId)
		);	
		while($row = $ilDB->fetchAssoc($res))
		{
			$row['attachments'] = unserialize(stripslashes($row['attachments']));
			$row['m_type'] = unserialize(stripslashes($row['m_type']));			
			$mails['set'][] = $row;
		}
		
		return $mails;
	}	
}