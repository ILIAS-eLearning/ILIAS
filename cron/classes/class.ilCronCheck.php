<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

class ilCronCheck
{
	function ilCronCheck()
	{
		global $ilLog;

		$this->log =& $ilLog;
	}

	function start()
	{
		global $ilias;
		
		include_once('Services/LDAP/classes/class.ilLDAPCronSynchronization.php');
		$ldap_sync = new ilLDAPCronSynchronization();
		$ldap_sync->start();

		// Check user accounts if enabled in settings
		if($ilias->getSetting('cron_user_check'))
		{
			include_once './cron/classes/class.ilCronCheckUserAccounts.php';

			$check_ua =& new ilCronCheckUserAccounts();
			$check_ua->check();
		}

		// Start Link check
		if($ilias->getSetting('cron_link_check'))
		{
			include_once './cron/classes/class.ilCronLinkCheck.php';

			$check_lnk =& new ilCronLinkCheck();
			$check_lnk->check();

		}

		// Start web resource check
		if($ilias->getSetting('cron_web_resource_check'))
		{
			include_once './cron/classes/class.ilCronWebResourceCheck.php';

			$check_lnk =& new ilCronWebResourceCheck();
			$check_lnk->check();
		}
		// Start lucene indexer
		if($ilias->getSetting("cron_lucene_index"))
		{
			include_once './Services/Search/classes/Lucene/class.ilLuceneIndexer.php';

			$lucene_ind =& new ilLuceneIndexer();
			$lucene_ind->index();
		}

		// Start sending forum notifications
		if($ilias->getSetting('forum_notification') == 2)
		{
			include_once './cron/classes/class.ilCronForumNotification.php';

			$frm_not =& new ilCronForumNotification();
			$frm_not->sendNotifications();

		}

		// Start sending mail notifications
		if($ilias->getSetting('mail_notification') == 1)
		{
			include_once './cron/classes/class.ilCronMailNotification.php';

			$mail_not =& new ilCronMailNotification();
			$mail_not->sendNotifications();
		}

	}
}




?>
