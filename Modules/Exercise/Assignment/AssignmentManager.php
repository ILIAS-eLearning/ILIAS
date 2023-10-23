<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\InternalDomainService;

class AssignmentManager
{
    public const TYPE_ALL = "all";
    public const TYPE_ONGOING = "ongoing";
    public const TYPE_FUTURE = "future";
    public const TYPE_PAST = "past";
    protected int $user_id;
    protected \ilLanguage $lng;
    protected InternalDomainService $domain;
    protected AssignmentsDBRepository $repo;
    protected int $obj_id;
    protected int $ref_id;

    public function __construct(
        InternalRepoService $repo_service,
        InternalDomainService $domain_service,
        int $ref_id,
        int $user_id
    ) {
        $this->ref_id = $ref_id;
        $this->obj_id = \ilObject::_lookupObjId($ref_id);
        $this->domain = $domain_service;
        $this->repo = $repo_service->assignment()->assignments();
        $this->lng = $domain_service->lng();
        $this->user_id = $user_id;
    }

    protected function getExcRefId(): int
    {
        return $this->ref_id;
    }

    protected function getExcId(): int
    {
        return $this->obj_id;
    }

    public function getValidListMode(string $mode): string
    {
        if (!in_array($mode, [self::TYPE_ONGOING,self::TYPE_FUTURE,self::TYPE_PAST,self::TYPE_ALL])) {
            return self::TYPE_ONGOING;
        }
        return $mode;
    }

    public function getListModes(): array
    {
        return [
            self::TYPE_ONGOING => $this->lng->txt("exc_ongoing"),
            self::TYPE_FUTURE => $this->lng->txt("exc_future"),
            self::TYPE_PAST => $this->lng->txt("exc_past"),
            self::TYPE_ALL => $this->lng->txt("exc_all")
        ];
    }

    public function getListModeLabel(string $mode): string
    {
        $modes = $this->getListModes();
        return $modes[$mode] ?? "";
    }

    /**
     * @return iterable<Assignment>
     */
    public function getList(string $mode): \Iterator
    {
        foreach ($this->repo->getList($this->getExcId()) as $ass) {
            $state = $this->domain->assignment()->state($ass->getId(), $this->user_id);
            if ($mode === self::TYPE_PAST && $state->hasEnded()) {
                yield $ass;
            }
            if ($mode === self::TYPE_FUTURE && $state->isFuture()) {
                yield $ass;
            }
            if ($mode === self::TYPE_ONGOING && !$state->hasEnded() && !$state->isFuture()) {
                yield $ass;
            }
        }
    }

    public function get(int $ass_id): Assignment
    {
        $ass = $this->repo->get($this->getExcId(), $ass_id);
        if (is_null($ass)) {
            throw new \ilExerciseException("Assignment not found (" . $this->getExcId() . "," . $ass_id . ").");
        }
        return $ass;
    }

}
