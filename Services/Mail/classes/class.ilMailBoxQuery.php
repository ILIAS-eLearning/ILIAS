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
			   		. 'AND folder_id = %s ' 
					. 'UNION ALL '
					. 'SELECT COUNT(mail_id) cnt FROM mail '
					. 'LEFT JOIN usr_data ON usr_id = sender_id '
			   		. 'WHERE user_id = %s '
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
		
		// item query
		$query = 'SELECT mail.* cnt FROM mail '
			   . 'LEFT JOIN usr_data ON usr_id = sender_id '
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
		if(strlen(self::$orderColumn) &&
		   $ilDB->tableColumnExists('mail', strtolower(self::$orderColumn)))
		{
			$query .= ' ORDER BY '.strtolower(self::$orderColumn).' '.$orderDirection;
		}
		else if(self::$orderColumn == 'from')
		{			
			$query .= ' ORDER BY '
				    . ' firstname '.$orderDirection.', '
				    . ' lastname '.$orderDirection.', '
				    . ' login '.$orderDirection.', '
				    . ' import_name '.$orderDirection;
		}
		else
		{
			$query .= ' ORDER BY send_time DESC';
		}
		
		$ilDB->setLimit(self::$limit, self::$offset);
		$res = $ilDB->queryf(
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
?>