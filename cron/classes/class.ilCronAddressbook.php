<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 * @version $Id:$
 */
class ilCronAddressbook
{
	public function syncAddressbook()
	{
		global $ilDB;

		if($ilDB->getDBType() == 'oracle')
		{
			$res1 = $ilDB->queryF('
				SELECT addressbook.addr_id, 
					   usr_data.firstname,
					   usr_data.lastname, 
					   (CASE WHEN epref.value = %s THEN usr_data.email ELSE addressbook.email END) email
				FROM addressbook
				INNER JOIN usr_data ON usr_data.login = addressbook.login
				INNER JOIN usr_pref ppref ON ppref.usr_id = usr_data.usr_id AND ppref.keyword = %s AND ppref.value != %s
				LEFT JOIN usr_pref epref ON epref.usr_id = usr_data.usr_id AND epref.keyword = %s
				WHERE addressbook.auto_update = %s',
				array('text', 'text', 'text', 'text', 'integer'),
				array('y', 'public_profile', 'n', 'public_email', 1)
			);

			$stmt = $ilDB->prepare('
				UPDATE addressbook 
				SET firstname = ?,
					lastname = ?,
					email = ?
				WHERE addr_id = ?',
				array('text','text','text', 'integer')
			);

			while($row = $ilDB->fetchAssoc($res1))
			{
				$ilDB->execute($stmt, array($row['firstname'], $row['lastname'], $row['email'], $row['addr_id']));
			}
		}
		else
		{
			$ilDB->queryF('
				UPDATE addressbook
				INNER JOIN usr_data ON usr_data.login = addressbook.login
				INNER JOIN usr_pref ppref ON ppref.usr_id = usr_data.usr_id AND ppref.keyword = %s AND ppref.value != %s
				LEFT JOIN usr_pref epref ON epref.usr_id = usr_data.usr_id AND epref.keyword = %s
				SET
				addressbook.firstname = usr_data.firstname,
				addressbook.lastname = usr_data.lastname,
				addressbook.email =  (CASE WHEN epref.value = %s THEN usr_data.email ELSE addressbook.email  END)
				WHERE addressbook.auto_update = %s',
				array('text', 'text', 'text', 'text', 'integer'),
				array('public_profile', 'n', 'public_email', 'y', 1)
			);
		}
	}
}
?>
