<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Nadia Krzywon
* @version $Id$
*/
class ilMailForm
{
	private $allow_smtp = null;
	private $user_id = null;
	private $setMap = array();	
	private $result;
    private $max_entries = 20;
	
	/**
	 * 
	 * Constructor
	 * 
	 * @access	public
	 * 
	 */
	public function __construct()
	{
		global $ilUser, $rbacsystem;
		
		$this->allow_smtp = $rbacsystem->checkAccess('smtp_mail', MAIL_SETTINGS_ID);
		$this->user_id = $ilUser->getId();
		
		$this->result = array();			
	}
	
	/**
	 * 
	 * Adds a result for mail recipient auto complete
	 *
	 * @access	private
	 * @throws	ilException
	 * 
	 */
	private function addResult($login, $firstname, $lastname, $type) 
	{
		if(count($this->result) > $this->max_entries)
		{
			throw new ilException('exceeded_max_entries');
		}

		if (isset($this->setMap[$login]))
			return;

		$tmp = new stdClass();			
		$tmp->value = $login;

		$label = $login;			
		if($firstname && $lastname)
		{
			$label .= " [" . $firstname . ", " . $lastname . "]";
		}
		$tmp->label = $label;

		$this->result[] = $tmp;

		$this->setMap[$login] = 1;
	}	

	/**
	 * 
	 * Called by class ilMailFormGUI
	 * 
	 * @param	string		search string surrounded with wildcards
	 * @param	string		native search string
	 * @return	stdClass	search result as an object of type stdClass
	 * @access	public
	 * 
	 */
	public function getRecipientAsync($a_search, $a_native_search)
	{
		global $ilDB;		
		
		$query =
			"SELECT DISTINCT
				abook.login login,
				abook.firstname firstname,
				abook.lastname lastname,
				'addressbook' type
			FROM addressbook abook
			WHERE abook.user_id = ".$ilDB->quote($this->user_id,'integer')."
			AND abook.login IS NOT NULL
			AND (". $ilDB->like('abook.login', 'text', $a_search)."
					OR ".$ilDB->like('abook.firstname', 'text', $a_search)."
					OR ".$ilDB->like('abook.lastname', 'text', $a_search)."
			)";

		$union_query_1 = "SELECT DISTINCT
				abook.email login,
				abook.firstname firstname,
				abook.lastname lastname,
				'addressbook' type
			FROM addressbook abook
			WHERE abook.user_id = ".$ilDB->quote($this->user_id,'integer')."
			AND abook.login IS NULL
			AND (".$ilDB->like('abook.email', 'text', $a_search)."
					OR ".$ilDB->like('abook.firstname', 'text', $a_search)."
					OR ".$ilDB->like('abook.lastname', 'text', $a_search)."
			)";

		$union_query_2 = "SELECT DISTINCT
				mail.rcp_to login,
				'' firstname,
				'' lastname,
				'mail' type
			FROM mail
			WHERE ".$ilDB->like('mail.rcp_to', 'text', $a_search)."
			AND sender_id = ".$ilDB->quote($this->user_id,'integer')."
			AND mail.sender_id = mail.user_id";
		
		$queries = array(
			'addressbook_1' => $query,
			'mail' => $union_query_2
		);
		
		if($this->allow_smtp == 1)
			$queries['addressbook_2'] = $union_query_1;
				
		include_once 'Services/Utilities/classes/class.ilStr.php';

		try
		{
			// MySql: Join the array values for mysql to one select statement
			if($ilDB->getDbType() != 'oracle')
				$queries['all'] = implode(' UNION ', $queries);				
			
			foreach($queries as $type => $query)
			{
				// Oracle: Distincts do no work with clobs
				if('mail' == $type && $ilDB->getDbType() == 'oracle')
				{
					$query = str_replace('DISTINCT', '', $query);
				}				
				
				$ilDB->setLimit(0,20);
				$query_res = $ilDB->query( $query );
				
				while($row = $ilDB->fetchObject($query_res))
				{
					if($row->type == 'mail')
					{
						if(strpos($row->login, ',') || strpos($row->login, ';'))
						{
							$parts = preg_split("/[ ]*[;,][ ]*/", trim($row->login));
							foreach($parts as $part)
							{
								if(ilStr::strPos(ilStr::strToLower($part), ilStr::strToLower($a_native_search)) !== false)
								{
									$this->addResult($part, '', '', 'mail');
								}						
							}
						}
						else
						{
							$this->addResult($row->login, '', '', 'mail');
						}
					}				
					else
					{
						$this->addResult($row->login, $row->firstname, $row->lastname, 'addressbook');
					}
				}
			}
		} catch(ilException $e) {}
		
		return $this->result;
	}
}
?>