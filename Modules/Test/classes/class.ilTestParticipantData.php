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
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    private array $activeIdsFilter;

    private array $userIdsFilter;

    private array $anonymousIdsFilter;

    private array $byActiveId;

    private array $byUserId;

    private array $byAnonymousId;

    /**
     * @var callable
     */
    protected $participantAccessFilter;

    protected bool $scoredParticipantsFilterEnabled;

    public function __construct(ilDBInterface $db, ilLanguage $lng)
    {
        $this->db = $db;
        $this->lng = $lng;

        $this->activeIdsFilter = array();
        $this->userIdsFilter = array();
        $this->anonymousIdsFilter = array();

        $this->byActiveId = array();
        $this->byUserId = array();
        $this->byAnonymousId = array();

        $this->scoredParticipantsFilterEnabled = false;
    }

    /**
     * @return callable
     */
    public function getParticipantAccessFilter(): ?callable
    {
        return $this->participantAccessFilter;
    }

    /**
     * @param callable $participantAccessFilter
     */
    public function setParticipantAccessFilter($participantAccessFilter): void
    {
        $this->participantAccessFilter = $participantAccessFilter;
    }

    /**
     * @return bool
     */
    public function isScoredParticipantsFilterEnabled(): bool
    {
        return $this->scoredParticipantsFilterEnabled;
    }

    /**
     * @param bool $scoredParticipantsFilterEnabled
     */
    public function setScoredParticipantsFilterEnabled($scoredParticipantsFilterEnabled): void
    {
        $this->scoredParticipantsFilterEnabled = $scoredParticipantsFilterEnabled;
    }

    public function load($testId): void
    {
        $this->byActiveId = array();
        $this->byUserId = array();

        $query = "
			SELECT		ta.active_id,
						ta.user_fi user_id,
						ta.anonymous_id,
						ud.firstname,
						ud.lastname,
						ud.login,
						ud.matriculation
			FROM		tst_active ta
			LEFT JOIN	usr_data ud
			ON 			ud.usr_id = ta.user_fi
			WHERE		test_fi = %s
			AND			{$this->getConditionalExpression()}
			AND 		{$this->getScoredParticipantsFilterExpression()}
		";

        $res = $this->db->queryF($query, array('integer'), array($testId));

        $rows = array();
        $accessFilteredUsrIds = array();

        while ($row = $this->db->fetchAssoc($res)) {
            $accessFilteredUsrIds[] = $row['user_id'];
            $rows[] = $row;
        }

        if (is_callable($this->getParticipantAccessFilter(), true)) {
            $accessFilteredUsrIds = call_user_func_array($this->getParticipantAccessFilter(), [$accessFilteredUsrIds]);
        }

        foreach ($rows as $row) {
            if (!in_array($row['user_id'], $accessFilteredUsrIds)) {
                continue;
            }

            $this->byActiveId[ $row['active_id'] ] = $row;

            if ($row['user_id'] == ANONYMOUS_USER_ID) {
                $this->byAnonymousId[ $row['anonymous_id'] ] = $row;
            } else {
                $this->byUserId[ $row['user_id'] ] = $row;
            }
        }
    }

    public function getScoredParticipantsFilterExpression(): string
    {
        if ($this->isScoredParticipantsFilterEnabled()) {
            return "ta.last_finished_pass = ta.last_started_pass";
        }

        return '1 = 1';
    }

    public function getConditionalExpression(): string
    {
        $conditions = array();

        if (count($this->getActiveIdsFilter())) {
            $conditions[] = $this->db->in('active_id', $this->getActiveIdsFilter(), false, 'integer');
        }

        if (count($this->getUserIdsFilter())) {
            $conditions[] = $this->db->in('user_fi', $this->getUserIdsFilter(), false, 'integer');
        }

        if (count($this->getAnonymousIdsFilter())) {
            $conditions[] = $this->db->in('anonymous_id', $this->getAnonymousIdsFilter(), false, 'integer');
        }

        if (count($conditions)) {
            return '(' . implode(' OR ', $conditions) . ')';
        }

        return '1 = 1';
    }

    public function setActiveIdsFilter($activeIdsFilter): void
    {
        $this->activeIdsFilter = $activeIdsFilter;
    }

    public function getActiveIdsFilter(): array
    {
        return $this->activeIdsFilter;
    }

    public function setUserIdsFilter($userIdsFilter): void
    {
        $this->userIdsFilter = $userIdsFilter;
    }

    public function getUserIdsFilter(): array
    {
        return $this->userIdsFilter;
    }

    public function setAnonymousIdsFilter($anonymousIdsFilter): void
    {
        $this->anonymousIdsFilter = $anonymousIdsFilter;
    }

    public function getAnonymousIdsFilter(): array
    {
        return $this->anonymousIdsFilter;
    }

    public function getActiveIds(): array
    {
        return array_keys($this->byActiveId);
    }

    public function getUserIds(): array
    {
        return array_keys($this->byUserId);
    }

    public function getAnonymousIds(): array
    {
        return array_keys($this->byAnonymousId);
    }

    public function getUserIdByActiveId($activeId)
    {
        return $this->byActiveId[$activeId]['user_id'];
    }

    public function getActiveIdByUserId($userId)
    {
        return $this->byUserId[$userId]['active_id'] ?? null;
    }

    public function getConcatedFullnameByActiveId($activeId): string
    {
        return "{$this->byActiveId[$activeId]['firstname']} {$this->byActiveId[$activeId]['lastname']}";
    }

    public function getFormatedFullnameByActiveId($activeId): string
    {
        return $this->buildFormatedFullname($this->byActiveId[$activeId]);
    }

    public function getFileSystemCompliantFullnameByActiveId($activeId): string
    {
        $fullname = str_replace(' ', '', $this->byActiveId[$activeId]['lastname']);
        $fullname .= '_' . str_replace(' ', '', $this->byActiveId[$activeId]['firstname']);
        $fullname .= '_' . $this->byActiveId[$activeId]['login'];

        return ilFileUtils::getASCIIFilename($fullname);
    }

    public function getOptionArray(): array
    {
        $options = array();

        foreach ($this->byActiveId as $activeId => $usrData) {
            $options[$activeId] = $this->buildFormatedFullname($usrData);
        }

        asort($options);

        return $options;
    }

    private function buildFormatedFullname($usrData): string
    {
        return sprintf(
            $this->lng->txt('tst_participant_fullname_pattern'),
            $usrData['firstname'],
            $usrData['lastname']
        );
    }

    public function getAnonymousActiveIds(): array
    {
        $anonymousActiveIds = array();

        foreach ($this->byActiveId as $activeId => $active) {
            if ($active['user_id'] == ANONYMOUS_USER_ID) {
                $anonymousActiveIds[] = $activeId;
            }
        }

        return $anonymousActiveIds;
    }

    public function getUserDataByActiveId($activeId)
    {
        if (isset($this->byActiveId[$activeId])) {
            return $this->byActiveId[$activeId];
        }

        return null;
    }
}
