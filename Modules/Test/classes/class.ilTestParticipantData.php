<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestParticipantData
{
	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var array
	 */
	private $activeIds;

	/**
	 * @var array
	 */
	private $userIds;

	/**
	 * @var array
	 */
	private $anonymousIds;

	/**
	 * @var array
	 */
	private $byActiveId;

	/**
	 * @var array
	 */
	private $byUserId;

	/**
	 * @var array
	 */
	private $byAnonymousId;
	
	public function __construct(ilDB $db, ilLanguage $lng)
	{
		$this->db = $db;
		$this->lng = $lng;

		$this->activeIds = array();
		$this->userIds = array();
		$this->anonymousIds = array();

		$this->byActiveId = array();
		$this->byUserId = array();
		$this->byAnonymousId = array();
	}
	
	public function load($testId)
	{
		$this->byActiveId = array();
		$this->byUserId   = array();

		$query = "
			SELECT		ta.active_id,
						ta.user_fi user_id,
						ta.anonymous_id,
						ud.firstname,
						ud.lastname
			FROM		tst_active ta
			LEFT JOIN	usr_data ud
			ON 			ud.usr_id = ta.user_fi
			WHERE		test_fi = %s
			AND			{$this->getConditionalExpression()}
		";
		
		$res = $this->db->queryF($query, array('integer'), array($testId));
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->byActiveId[ $row['active_id'] ] = $row;
			
			if( $row['user_id'] == ANONYMOUS_USER_ID )
			{
				$this->byAnonymousId[ $row['anonymous_id'] ] = $row;
			}
			else
			{
				$this->byUserId[ $row['user_id'] ] = $row;
			}
		}

		$this->setActiveIds(array_keys($this->byActiveId));
		$this->setUserIds(array_keys($this->byUserId));
		$this->setAnonymousIds(array_keys($this->byAnonymousId));
	}
	
	public function getConditionalExpression()
	{
		$conditions = array();
		
		if( count($this->getActiveIds()) )
		{
			$conditions[] = $this->db->in('active_id', $this->getActiveIds(), false, 'integer');
		}

		if( count($this->getUserIds()) )
		{
			$conditions[] = $this->db->in('user_fi', $this->getUserIds(), false, 'integer');
		}

		if( count($this->getAnonymousIds()) )
		{
			$conditions[] = $this->db->in('anonymous_id', $this->getAnonymousIds(), false, 'integer');
		}

		if( count($conditions) )
		{
			return '('.implode(' OR ', $conditions).')';
		}

		return '1 = 1';
	}

	public function setActiveIds($activeIds)
	{
		$this->activeIds = $activeIds;
	}

	public function getActiveIds()
	{
		return $this->activeIds;
	}

	public function setUserIds($userIds)
	{
		$this->userIds = $userIds;
	}

	public function getUserIds()
	{
		return $this->userIds;
	}

	public function setAnonymousIds($anonymousIds)
	{
		$this->anonymousIds = $anonymousIds;
	}

	public function getAnonymousIds()
	{
		return $this->anonymousIds;
	}
	
	public function getUserIdByActiveId($activeId)
	{
		return $this->byActiveId[$activeId]['user_id'];
	}

	public function getActiveIdByUserId($userId)
	{
		return $this->byUserId[$userId]['active_id'];
	}
	
	public function getConcatedFullnameByActiveId($activeId)
	{
		return "{$this->byActiveId[$activeId]['firstname']} {$this->byActiveId[$activeId]['lastname']}";
	}

	public function getFormatedFullnameByActiveId($activeId)
	{
		return $this->buildFormatedFullname($this->byActiveId[$activeId]);
	}
	
	public function getOptionArray()
	{
		$options = array();
		
		foreach($this->byActiveId as $activeId => $usrData)
		{
			$options[$activeId] = $this->buildFormatedFullname($usrData);
		}
		
		asort($options);
		
		return $options;
	}
	
	private function buildFormatedFullname($usrData)
	{
		return sprintf(
			$this->lng->txt('tst_participant_fullname_pattern'), $usrData['firstname'], $usrData['lastname']
		);
	}
	
	public function getAnonymousActiveIds()
	{
		$anonymousActiveIds = array();
		
		foreach($this->byActiveId as $activeId => $active)
		{
			if($active['user_id'] == ANONYMOUS_USER_ID)
			{
				$anonymousActiveIds[] = $activeId;
			}
		}
		
		return $anonymousActiveIds;
	}
}