<?php

declare(strict_types=1);

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

/**
 * ilStudyProgrammeUserTable provides a flattened list of progresses at a programme-node.
 */
class ilStudyProgrammeUserTableRow
{
    protected PRGProgressId $id;
    protected int $ass_id;
    protected int $usr_id;
    protected int $pgs_id;
    protected bool $is_root_progress;
    protected int $status_raw;
    protected bool $active_raw;

    protected ilUserDefinedData $udf;

    protected string $active;
    protected string $firstname;
    protected string $lastname;
    protected string $login;
    protected string $orgus;
    protected string $gender;
    protected string $status;
    protected string $completion_date;
    protected ?int $completion_by_obj_id;
    protected string $completion_by;
    protected string $points_reachable;
    protected string $points_required;
    protected string $points_current;
    protected string $custom_plan;
    protected string $belongs_to;
    protected string $assign_date;
    protected string $assigned_by;
    protected string $deadline;
    protected string $expiry_date;
    protected string $validity;
    protected string $restart_date;

    public function __construct(int $ass_id, int $usr_id, int $node_obj_id, bool $is_root_progress)
    {
        $this->id = new PRGProgressId($ass_id, $usr_id, $node_obj_id);
        $this->ass_id = $ass_id;
        $this->usr_id = $usr_id;
        $this->node_id = $node_obj_id;
        $this->is_root_progress = $is_root_progress;
    }

    public function getId(): PRGProgressId
    {
        return $this->id;
    }
    public function getAssignmentId(): int
    {
        return $this->ass_id;
    }
    public function getUsrId(): int
    {
        return $this->usr_id;
    }
    public function getNodeId(): int
    {
        return $this->node_id;
    }
    public function isRootProgress(): bool
    {
        return $this->is_root_progress;
    }

    public function withUserActiveRaw(bool $active_raw): self
    {
        $clone = clone $this;
        $clone->active_raw = $active_raw;
        return $clone;
    }
    public function isUserActiveRaw(): bool
    {
        return $this->active_raw;
    }
    public function withUserActive(string $active): self
    {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }
    public function getUserActive(): string
    {
        return $this->active;
    }

    public function withFirstname(string $firstname): self
    {
        $clone = clone $this;
        $clone->firstname = $firstname;
        return $clone;
    }
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function withLastname(string $lastname): self
    {
        $clone = clone $this;
        $clone->lastname = $lastname;
        return $clone;
    }
    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getName(): string
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    public function withLogin(string $login): self
    {
        $clone = clone $this;
        $clone->login = $login;
        return $clone;
    }
    public function getLogin(): string
    {
        return $this->login;
    }

    public function withOrgUs(string $orgus): self
    {
        $clone = clone $this;
        $clone->orgus = $orgus;
        return $clone;
    }
    public function getOrgUs(): string
    {
        return $this->orgus;
    }

    public function withGender(string $gender): self
    {
        $clone = clone $this;
        $clone->gender = $gender;
        return $clone;
    }
    public function getGender(): string
    {
        return $this->gender;
    }

    public function withUDF(ilUserDefinedData $udf): self
    {
        $clone = clone $this;
        $clone->udf = $udf;
        return $clone;
    }
    public function getUdf(string $field)
    {
        return $this->udf->get($field);
    }

    public function withStatus(string $status): self
    {
        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function withStatusRaw(int $status_raw): self
    {
        $clone = clone $this;
        $clone->status_raw = $status_raw;
        return $clone;
    }
    public function getStatusRaw(): int
    {
        return $this->status_raw;
    }

    public function withCompletionDate(string $completion_date): self
    {
        $clone = clone $this;
        $clone->completion_date = $completion_date;
        return $clone;
    }
    public function getCompletionDate(): string
    {
        return $this->completion_date;
    }

    public function withCompletionBy(string $completion_by): self
    {
        $clone = clone $this;
        $clone->completion_by = $completion_by;
        return $clone;
    }
    public function getCompletionBy(): string
    {
        return $this->completion_by;
    }

    public function withCompletionByObjId(?int $obj_id): self
    {
        $clone = clone $this;
        $clone->completion_by_obj_id = $obj_id;
        return $clone;
    }

    public function getCompletionByObjId(): ?int
    {
        return $this->completion_by_obj_id;
    }

    public function withPointsReachable(string $points_reachable): self
    {
        $clone = clone $this;
        $clone->points_reachable = $points_reachable;
        return $clone;
    }
    public function getPointsReachable(): string
    {
        return $this->points_reachable;
    }
    public function withPointsRequired(string $points_required): self
    {
        $clone = clone $this;
        $clone->points_required = $points_required;
        return $clone;
    }
    public function getPointsRequired(): string
    {
        return $this->points_required;
    }
    public function withPointsCurrent(string $points_current): self
    {
        $clone = clone $this;
        $clone->points_current = $points_current;
        return $clone;
    }
    public function getPointsCurrent(): string
    {
        return $this->points_current;
    }
    public function withCustomPlan(string $custom_plan): self
    {
        $clone = clone $this;
        $clone->custom_plan = $custom_plan;
        return $clone;
    }
    public function getCustomPlan(): string
    {
        return $this->custom_plan;
    }

    public function withBelongsTo(string $belongs_to): self
    {
        $clone = clone $this;
        $clone->belongs_to = $belongs_to;
        return $clone;
    }
    public function getBelongsTo(): string
    {
        return $this->belongs_to;
    }

    public function withAssignmentDate(string $assign_date): self
    {
        $clone = clone $this;
        $clone->assign_date = $assign_date;
        return $clone;
    }
    public function getAssignmentDate(): string
    {
        return $this->assign_date;
    }

    public function withAssignmentBy(string $assigned_by): self
    {
        $clone = clone $this;
        $clone->assigned_by = $assigned_by;
        return $clone;
    }
    public function getAssignmentBy(): string
    {
        return $this->assigned_by;
    }

    public function withDeadline(string $deadline): self
    {
        $clone = clone $this;
        $clone->deadline = $deadline;
        return $clone;
    }
    public function getDeadline(): string
    {
        return $this->deadline;
    }

    public function withExpiryDate(string $expiry_date): self
    {
        $clone = clone $this;
        $clone->expiry_date = $expiry_date;
        return $clone;
    }
    public function getExpiryDate(): string
    {
        return $this->expiry_date;
    }

    public function withValidity(string $validity): self
    {
        $clone = clone $this;
        $clone->validity = $validity;
        return $clone;
    }
    public function getValidity(): string
    {
        return $this->validity;
    }

    public function withRestartDate(string $restart_date): self
    {
        $clone = clone $this;
        $clone->restart_date = $restart_date;
        return $clone;
    }
    public function getRestartDate(): string
    {
        return $this->restart_date;
    }

    public function toArray(): array
    {
        return [
            'prgrs_id' => (string)$this->getId(),
            'name' => $this->getName(),
            'active_raw' => $this->isUserActiveRaw(),
            'active' => $this->getUserActive(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'login' => $this->getLogin(),
            'orgus' => $this->getOrgUs(),
            'gender' => $this->getGender(),
            'status' => $this->getStatus(),
            'completion_date' => $this->getCompletionDate(),
            'completion_by' => $this->getCompletionBy(),
            'points_reachable' => $this->getPointsReachable(),
            'points_required' => $this->getPointsRequired(),
            'points_current' => $this->getPointsCurrent(),
            'custom_plan' => $this->getCustomPlan(),
            'belongs_to' => $this->getBelongsTo(),
            'assign_date' => $this->getAssignmentDate(),
            'assigned_by' => $this->getAssignmentBy(),
            'deadline' => $this->getDeadline(),
            'expiry_date' => $this->getExpiryDate(),
            'validity' => $this->getValidity()
        ];
    }
}
