<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Type/class.ilBaseMailAddressType.php';
require_once 'Services/Mail/classes/Address/Parser/class.ilMailRfc822AddressParserFactory.php';

/**
 * Class ilMailRoleAddressType
 * @author Werner Randelshofer <wrandels@hsw.fhz.ch>
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailRoleAddressType extends ilBaseMailAddressType
{
	/**
	 * @var array
	 */
	protected static $role_ids_by_address = array();

	/**
	 * @var array
	 */
	protected static $may_send_to_global_roles = array();

	/**
	 * @param ilMailAddress $a_address
	 * @return array
	 */
	protected static function getRoleIdsByAddress(ilMailAddress $a_address)
	{
		$address = $a_address->getMailbox() . '@' . $a_address->getHost();

		if(!isset(self::$role_ids_by_address[$address]))
		{
			self::$role_ids_by_address[$address] = self::searchRolesByMailboxAddressList($address);
		}

		return self::$role_ids_by_address[$address];
	}

	/**
	 * @param int $a_sender_id
	 * @return bool
	 */
	protected function maySendToGlobalRole($a_sender_id)
	{
		if(!isset(self::$may_send_to_global_roles[$a_sender_id]))
		{
			if($a_sender_id == ANONYMOUS_USER_ID)
			{
				self::$may_send_to_global_roles[$a_sender_id] = true;
			}
			else
			{
				require_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
				self::$may_send_to_global_roles[$a_sender_id] = $this->rbacsystem->checkAccessOfUser(
					$a_sender_id, 'mail_to_global_roles', ilMailGlobalServices::getMailObjectRefId()
				);
			}
		}

		return self::$may_send_to_global_roles[$a_sender_id];
	}

	/**
	 * {@inheritdoc}
	 */
	public function isValid($a_sender_id)
	{
		$role_ids = self::getRoleIdsByAddress($this->address);
		if(!self::maySendToGlobalRole($a_sender_id))
		{
			foreach($role_ids as $role_id)
			{
				if($this->rbacreview->isGlobalRole($role_id))
				{
					$this->errors[] = array('mail_to_global_roles_not_allowed', $this->address->getMailbox());
					return false;
				}
			}
		}

		if(count($role_ids) == 0)
		{
			$this->errors[] = array('mail_recipient_not_found', $this->address->getMailbox());
			return false;
		}
		else if(count($role_ids) > 1)
		{
			$this->errors[] = array('mail_multiple_role_recipients_found', $this->address->getMailbox(), implode(',', $role_ids));
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolve()
	{
		$usr_ids = array();

		$role_ids = self::getRoleIdsByAddress($this->address);

		if(count($role_ids) > 0)
		{
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Found the following role ids for address '%s': %s", $this->address->getMailbox() . '@' . $this->address->getHost(), implode(', ', array_unique($role_ids))
			));

			foreach($role_ids as $role_id)
			{
				foreach($this->rbacreview->assignedUsers($role_id) as $usr_id)
				{
					$usr_ids[] = $usr_id;
				}
			}

			if(count($usr_ids) > 0)
			{
				ilLoggerFactory::getLogger('mail')->debug(sprintf(
					"Found the following user ids for roles determined by address '%s': %s", $this->address->getMailbox() . '@' . $this->address->getHost(), implode(', ', array_unique($usr_ids))
				));
			}
			else
			{
				ilLoggerFactory::getLogger('mail')->debug(sprintf(
					"Did not find any assigned users for roles determined by '%s'", $this->address->getMailbox() . '@' . $this->address->getHost()
				));
			}
		}
		else
		{
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Did not find any role (and user ids) for address '%s'", $this->address->getMailbox() . '@' . $this->address->getHost()
			));
		}

		return array_unique($usr_ids);
	}

	/**
	 * Finds all role ids that match the specified user friendly role mailbox address list.
	 *
	 * The role mailbox name address list is an e-mail address list according to IETF RFC 822:
	 *
	 * address list  = role mailbox, {"," role mailbox } ;
	 * role mailbox  = "#", local part, ["@" domain] ;
	 *
	 * Examples: The following role mailbox names are all resolved to the role il_crs_member_123:
	 *
	 *    #Course.A
	 *    #member@Course.A
	 *    #il_crs_member_123@Course.A
	 *    #il_crs_member_123
	 *    #il_crs_member_123@ilias
	 *
	 * Examples: The following role mailbox names are all resolved to the role il_crs_member_345:
	 *
	 *    #member@[English Course]
	 *    #il_crs_member_345@[English Course]
	 *    #il_crs_member_345
	 *    #il_crs_member_345@ilias
	 *
	 * If only the local part is specified, or if domain is equal to "ilias", ILIAS compares
	 * the title of role objects with local part. Only roles that are not in a trash folder
	 * are considered for the comparison.
	 *
	 * If a domain is specified, and if the domain is not equal to "ilias", ILIAS compares
	 * the title of objects with the domain. Only objects that are not in a trash folder are
	 * considered for the comparison. Then ILIAS searches for local roles which contain
	 * the local part in their title. This allows for abbreviated role names, e.g. instead of
	 * having to specify #il_grp_member_345@MyGroup, it is sufficient to specify #member@MyGroup.
	 *
	 * The address list may contain addresses thate are not role mailboxes. These addresses
	 * are ignored.
	 *
	 * If a role mailbox address is ambiguous, this function returns the ID's of all role
	 * objects that are possible recipients for the role mailbox address.
	 *
	 * If Pear Mail is not installed, then the mailbox address
	 * @param $a_address_list
	 * @param ilMailRfc822AddressParserFactory|null $parserFactory
	 * @param ilMailRfc822AddressParser|null $parser
	 * @return int[] Array with role ids that were found
	 * @internal param null $dic
	 * @internal param IETF $string RFX 822 address list containing role mailboxes.
	 */
	public static function searchRolesByMailboxAddressList(
		$a_address_list,
		ilMailRfc822AddressParserFactory $parserFactory = null,
		ilMailRfc822AddressParser $parser = null
	) {
		global $DIC;
		
		if ($parserFactory === null) {
			$parserFactory = new ilMailRfc822AddressParserFactory();
		}

		if ($parser === null) {
			$parser = $parserFactory->getParser($a_address_list);
		}

		$role_ids = array();
		$parsedList = $parser->parse();

		foreach($parsedList as $address)
		{
			$local_part = $address->getMailbox();
			if(strpos($local_part,'#') !== 0 && !($local_part{0} == '"' && $local_part{1} == "#"))
			{
				// A local-part which doesn't start with a '#' doesn't denote a role.
				// Therefore we can skip it.
				continue;
			}

			$local_part = substr($local_part, 1);

			/* If role contains spaces, eg. 'foo role', double quotes are added which have to be removed here.*/
			if($local_part{0} == '#' && $local_part{strlen($local_part) - 1} == '"')
			{
				$local_part = substr($local_part, 1);
				$local_part = substr($local_part, 0, strlen($local_part) - 1);
			}

			if(substr($local_part, 0, 8) == 'il_role_')
			{
				$role_id = substr($local_part, 8);
				$query = "SELECT t.tree ".
					"FROM rbac_fa fa ".
					"JOIN tree t ON t.child = fa.parent ".
					"WHERE fa.rol_id = ". $DIC->database()->quote($role_id,'integer')." ".
					"AND fa.assign = 'y' ".
					"AND t.tree = 1";
				$res = $DIC->database()->query($query);
				if($DIC->database()->numRows($res) > 0)
				{
					$role_ids[] = $role_id;
				}
				continue;
			}

			$domain = $address->getHost();
			if(strpos($domain,'[') == 0 && strrpos($domain,']'))
			{
				$domain = substr($domain,1,strlen($domain) - 2);
			}
			if(strlen($local_part) == 0)
			{
				$local_part = $domain;
				$address->setHost(ilMail::ILIAS_HOST);
				$domain = ilMail::ILIAS_HOST;
			}

			if(strtolower($address->getHost()) == ilMail::ILIAS_HOST)
			{
				// Search for roles = local-part in the whole repository
				$query = "SELECT dat.obj_id ".
					"FROM object_data dat ".
					"JOIN rbac_fa fa ON fa.rol_id = dat.obj_id ".
					"JOIN tree t ON t.child = fa.parent ".
					"WHERE dat.title =".$DIC->database()->quote($local_part,'text')." ".
					"AND dat.type = 'role' ".
					"AND fa.assign = 'y' ".
					"AND t.tree = 1";
			}
			else
			{
				// Search for roles like local-part in objects = host
				$query = "SELECT rdat.obj_id ".
					"FROM object_data odat ".
					"JOIN object_reference oref ON oref.obj_id = odat.obj_id ".
					"JOIN tree otree ON otree.child = oref.ref_id ".
					"JOIN rbac_fa rfa ON rfa.parent = otree.child ".
					"JOIN object_data rdat ON rdat.obj_id = rfa.rol_id ".
					"WHERE odat.title = ".$DIC->database()->quote($domain,'text')." ".
					"AND otree.tree = 1 ".
					"AND rfa.assign = 'y' ".
					"AND rdat.title LIKE ".
					$DIC->database()->quote('%'.preg_replace('/([_%])/','\\\\$1',$local_part).'%','text');
			}
			$res = $DIC->database()->query($query);

			$count = 0;
			while($row = $DIC->database()->fetchAssoc($res))
			{
				$role_ids[] = $row['obj_id'];

				$count++;
			}

			// Nothing found?
			// In this case, we search for roles = host.
			if($count == 0 && strtolower($address->getHost()) == ilMail::ILIAS_HOST)
			{
				$q = "SELECT dat.obj_id ".
					"FROM object_data dat ".
					"JOIN object_reference ref ON ref.obj_id = dat.obj_id ".
					"JOIN tree t ON t.child = ref.ref_id ".
					"WHERE dat.title = ".$DIC->database()->quote($domain ,'text')." ".
					"AND dat.type = 'role' ".
					"AND t.tree = 1 ";
				$res = $DIC->database()->query($q);

				while($row = $DIC->database()->fetchAssoc($res))
				{
					$role_ids[] = $row['obj_id'];
				}
			}
		}

		return $role_ids;
	}

	/**
	 * Returns the mailbox address of a role.
	 *
	 * Example 1: Mailbox address for an ILIAS reserved role name
	 * ----------------------------------------------------------
	 * The il_crs_member_345 role of the course object "English Course 1" is
	 * returned as one of the following mailbox addresses:
	 *
	 * a)   Course Member <#member@[English Course 1]>
	 * b)   Course Member <#il_crs_member_345@[English Course 1]>
	 * c)   Course Member <#il_crs_member_345>
	 *
	 * Address a) is returned, if the title of the object is unique, and
	 * if there is only one local role with the substring "member" defined for
	 * the object.
	 *
	 * Address b) is returned, if the title of the object is unique, but
	 * there is more than one local role with the substring "member" in its title.
	 *
	 * Address c) is returned, if the title of the course object is not unique.
	 *
	 *
	 * Example 2: Mailbox address for a manually defined role name
	 * -----------------------------------------------------------
	 * The "Admin" role of the category object "Courses" is
	 * returned as one of the following mailbox addresses:
	 *
	 * a)   Course Administrator <#Admin@Courses>
	 * b)   Course Administrator <#Admin>
	 * c)   Course Adminstrator <#il_role_34211>
	 *
	 * Address a) is returned, if the title of the object is unique, and
	 * if there is only one local role with the substring "Admin" defined for
	 * the course object.
	 *
	 * Address b) is returned, if the title of the object is not unique, but
	 * the role title is unique.
	 *
	 * Address c) is returned, if neither the role title nor the title of the
	 * course object is unique.
	 *
	 *
	 * Example 3: Mailbox address for a manually defined role title that can
	 *            contains special characters in the local-part of a
	 *            mailbox address
	 * --------------------------------------------------------------------
	 * The "Author Courses" role of the category object "Courses" is
	 * returned as one of the following mailbox addresses:
	 *
	 * a)   "#Author Courses"@Courses
	 * b)   Author Courses <#il_role_34234>
	 *
	 * Address a) is returned, if the title of the role is unique.
	 *
	 * Address b) is returned, if neither the role title nor the title of the
	 * course object is unique, or if the role title contains a quote or a
	 * backslash.
	 *
	 *
	 * @param $a_role_id
	 * @param bool $is_localize is_localize whether mailbox addresses should be localized
	 * @param ilMailRfc822AddressParserFactory|null $mailAddressParserFactory
	 * @return String mailbox address or null, if role does not exist.
	 * @internal param a $int role id
	 */
	public static function getRoleMailboxAddress(
		$a_role_id,
		$is_localize = true,
		ilMailRfc822AddressParserFactory $mailAddressParserFactory = null
	) {
		global $DIC;

		if ($mailAddressParserFactory === null) {
			$mailAddressParserFactory = new ilMailRfc822AddressParserFactory();
		}

		// Retrieve the role title and the object title.
		$query = "SELECT rdat.title role_title,odat.title object_title, ".
			" oref.ref_id object_ref ".
			"FROM object_data rdat ".
			"JOIN rbac_fa fa ON fa.rol_id = rdat.obj_id ".
			"JOIN tree rtree ON rtree.child = fa.parent ".
			"JOIN object_reference oref ON oref.ref_id = rtree.child ".
			"JOIN object_data odat ON odat.obj_id = oref.obj_id ".
			"WHERE rdat.obj_id = ".$DIC->database()->quote($a_role_id,'integer')." ".
			"AND fa.assign = 'y' ";
		$res = $DIC->database()->query($query);
		if(!$row = $DIC->database()->fetchObject($res))
		{
			return null;
		}

		$object_title = $row->object_title;
		$object_ref   = $row->object_ref;
		$role_title   = $row->role_title;

		// In a perfect world, we could use the object_title in the
		// domain part of the mailbox address, and the role title
		// with prefix '#' in the local part of the mailbox address.
		$domain = $object_title;
		$local_part = $role_title;


		// Determine if the object title is unique
		$q = "SELECT COUNT(DISTINCT dat.obj_id) count ".
			"FROM object_data dat ".
			"JOIN object_reference ref ON ref.obj_id = dat.obj_id ".
			"JOIN tree ON tree.child = ref.ref_id ".
			"WHERE title = ".$DIC->database()->quote($object_title,'text')." ".
			"AND tree.tree = 1 ";
		$res = $DIC->database()->query($q);
		$row = $DIC->database()->fetchObject($res);

		// If the object title is not unique, we get rid of the domain.
		if ($row->count > 1)
		{
			$domain = null;
		}

		// If the domain contains illegal characters, we get rid of it.
		//if (domain != null && preg_match('/[\[\]\\]|[\x00-\x1f]/',$domain))
		// Fix for Mantis Bug: 7429 sending mail fails because of brakets
		// Fix for Mantis Bug: 9978 sending mail fails because of semicolon
		if ($domain != null && preg_match('/[\[\]\\]|[\x00-\x1f]|[\x28-\x29]|[;]/',$domain))
		{
			$domain = null;
		}

		// If the domain contains special characters, we put square
		//   brackets around it.
		if ($domain != null &&
			(preg_match('/[()<>@,;:\\".\[\]]/',$domain) ||
				preg_match('/[^\x21-\x8f]/',$domain))
		)
		{
			$domain = '['.$domain.']';
		}

		// If the role title is one of the ILIAS reserved role titles,
		//     we can use a shorthand version of it for the local part
		//     of the mailbox address.
		if (strpos($role_title, 'il_') === 0 && $domain != null)
		{
			$unambiguous_role_title = $role_title;

			$pos = strpos($role_title, '_', 3) + 1;
			$local_part = substr(
				$role_title,
				$pos,
				strrpos($role_title, '_') - $pos
			);
		}
		else
		{
			$unambiguous_role_title = 'il_role_'.$a_role_id;
		}

		// Determine if the local part is unique. If we don't have a
		// domain, the local part must be unique within the whole repositry.
		// If we do have a domain, the local part must be unique for that
		// domain.
		if ($domain == null)
		{
			$q = "SELECT COUNT(DISTINCT dat.obj_id) count ".
				"FROM object_data dat ".
				"JOIN object_reference ref ON ref.obj_id = dat.obj_id ".
				"JOIN tree ON tree.child = ref.ref_id ".
				"WHERE title = ".$DIC->database()->quote($local_part,'text')." ".
				"AND tree.tree = 1 ";
		}
		else
		{
			$q = "SELECT COUNT(rd.obj_id) count ".
				"FROM object_data rd ".
				"JOIN rbac_fa fa ON rd.obj_id = fa.rol_id ".
				"JOIN tree t ON t.child = fa.parent ".
				"WHERE fa.assign = 'y' ".
				"AND t.child = ".$DIC->database()->quote($object_ref,'integer')." ".
				"AND rd.title LIKE ".$DIC->database()->quote(
					'%'.preg_replace('/([_%])/','\\\\$1', $local_part).'%','text')." ";
		}

		$res = $DIC->database()->query($q);
		$row = $DIC->database()->fetchObject($res);

		// if the local_part is not unique, we use the unambiguous role title
		//   instead for the local part of the mailbox address
		if ($row->count > 1)
		{
			$local_part = $unambiguous_role_title;
		}

		$use_phrase = true;

		// If the local part contains illegal characters, we use
		//     the unambiguous role title instead.
		if (preg_match('/[\\"\x00-\x1f]/',$local_part))
		{
			$local_part = $unambiguous_role_title;
		}
		else if(!preg_match('/^[\\x00-\\x7E]+$/i', $local_part))
		{
			// 2013-12-05: According to #12283, we do not accept umlauts in the local part
			$local_part = $unambiguous_role_title;
			$use_phrase = false;
		}

		// Add a "#" prefix to the local part
		$local_part = '#'.$local_part;

		// Put quotes around the role title, if needed
		if (preg_match('/[()<>@,;:.\[\]\x20]/',$local_part))
		{
			$local_part = '"'.$local_part.'"';
		}

		$mailbox = ($domain == null) ?
			$local_part :
			$local_part.'@'.$domain;

		if ($is_localize)
		{
			if (substr($role_title,0,3) == 'il_')
			{
				$phrase = $DIC->language()->txt(substr($role_title, 0, strrpos($role_title,'_')));
			}
			else
			{
				$phrase = $role_title;
			}

			if($use_phrase)
			{
				// make phrase RFC 822 conformant:
				// - strip excessive whitespace
				// - strip special characters
				$phrase = preg_replace('/\s\s+/', ' ', $phrase);
				$phrase = preg_replace('/[()<>@,;:\\".\[\]]/', '', $phrase);

				$mailbox = $phrase.' <'.$mailbox.'>';
			}
		}

		try
		{
			$parser = $mailAddressParserFactory->getParser($mailbox);
			$parser->parse();

			return $mailbox;
		}
		catch(ilException $e)
		{
			$res = $DIC->database()->query("SELECT title FROM object_data WHERE obj_id = " . $DIC->database()->quote($a_role_id ,'integer'));
			if($row = $DIC->database()->fetchObject($res))
			{
				return '#' . $row->title;
			}
			else
			{
				return null;
			}
		}
	}
}