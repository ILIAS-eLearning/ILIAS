<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Mail/classes/class.ilMailAutoCompleteRecipientProvider.php';

/**
 * Class ilMailAutoCompleteAddressbookLoginProvider
 */
class ilMailAutoCompleteAddressbookLoginProvider extends ilMailAutoCompleteRecipientProvider
{
	/**
	 * "Current" implementation of iterator interface
	 * @return  array
	 */
	public function current()
	{

		return array(
			'login'     => $this->data['login'],
			'firstname' => $this->data['firstname'],
			'lastname'  => $this->data['lastname']
		);
	}

	/**
	 * "Key" implementation of iterator interface
	 * @return  boolean true/false
	 */
	public function key()
	{
		return $this->data['login'];
	}

	/**
	 * "Rewind "implementation of iterator interface
	 */
	public function rewind()
	{

		if($this->res)
		{
			$this->db->free($this->res);
			$this->res = null;
		}

		$query     = "
			SELECT DISTINCT
				abook.login login,
				abook.firstname firstname,
				abook.lastname lastname
			FROM addressbook abook
			WHERE abook.user_id = " . $this->db->quote($this->user_id, 'integer') . "
			AND abook.login IS NOT NULL
			AND (" .
				$this->db->like('abook.login', 'text', $this->quoted_term) . " OR " .
				$this->db->like('abook.firstname', 'text', $this->quoted_term) . " OR " .
				$this->db->like('abook.lastname', 'text', $this->quoted_term) . "
				
			)";
		$this->res = $this->db->query($query);
	}
}