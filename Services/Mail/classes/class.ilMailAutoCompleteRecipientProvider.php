<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAutoCompleteRecipientProvider
 */
abstract class ilMailAutoCompleteRecipientProvider implements Iterator
{
	/**
	 * The database access object
	 * @var         ilDB
	 */
	protected $db;

	/**
	 * MDB2_Result_Common
	 * @var     MDB2_Result_Common
	 */
	protected $res;

	/**
	 * Holds the data of a tuple
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var string search term
	 */
	protected $quoted_term = '';

	/**
	 * @var string
	 */
	protected $term = '';

	/**
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * @param string $quoted_term
	 * @param string $term
	 */
	public function __construct($quoted_term, $term)
	{
		/**
		 * @var $ilDB   ilDB
		 * @var $ilUser ilObjUser
		 */
		global $ilDB, $ilUser;

		$this->db          = $ilDB;
		$this->quoted_term = $quoted_term;
		$this->term        = $term;
		$this->user_id     = $ilUser->getId();
	}

	/**
	 * "Valid" implementation of iterator interface
	 * @return  boolean true/false
	 */
	public function valid()
	{
		$this->data = $this->db->fetchAssoc($this->res);

		return is_array($this->data);
	}

	/**
	 * "Next" implementation of iterator interface
	 */
	public function next()
	{
	}

	/**
	 * Destructor
	 * Free the result
	 */
	public function __destruct()
	{
		if($this->res)
		{
			$this->db->free($this->res);
			$this->res = null;
		}
	}
}