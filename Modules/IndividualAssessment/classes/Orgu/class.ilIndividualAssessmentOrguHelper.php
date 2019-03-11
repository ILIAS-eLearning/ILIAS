<?php

declare(strict_types=1);

class ilIndividualAssessmentOrguHelper
{
	const PERMISSION_VIEW_LP = "read_learning_progress";
	const PERMISSION_EDIT_LP = "write_learning_progress";

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var ilOrgUnitUserAssignmentQueries
	 */
	protected $org_unit_user_assignment;

	public function __construct(int $ref_id, ilOrgUnitUserAssignmentQueries $org_unit_user_assignment)
	{
		$this->ref_id = $ref_id;
		$this->org_unit_user_assignment = $org_unit_user_assignment;
	}

	/**
	 * @param int[] $employees_ids
	 * @param ilIndividualAssessmentMember[] $entries
	 * @return ilIndividualAssessmentMember[]
	 */
	public function getRelevantMembersWhereUserHasAuthority(array $employees_ids, array $entries): array
	{
		return array_filter($entries, function(ilIndividualAssessmentMember $entry) use($employees_ids) {
			return in_array($entry->id(), $employees_ids);
		});
	}

	/**
	 * @return int[]
	 */
	public function getMembersWhereUserHasAuthorityAndEditOrViewPermission(int $user_id): array
	{
		return $this->getUserIdsWhereUserHasAuthority($user_id);
	}

	/**
	 * return int[]
	 */
	protected function getUserIdsWhereUserHasAuthority(int $user_id): array
	{
		$positions = $this->getPositionsOf($user_id);
		$positions = $this->filterPositionByPermission($positions);

		$user_ids = array();
		foreach ($positions as $position) {
			$result = array_map(function($u) {
					return (int)$u;
				},
				$this->getUserIdsByPositionAndUser($position, $user_id)
			);

			$user_ids = array_merge($user_ids, $result);
		}

		return array_unique($user_ids);
	}

	/**
	 * @return ilOrgUnitPosition[]
	 */
	protected function getPositionsOf(int $user_id): array
	{
		$assignments = $this->getAssignmentsOf($user_id);
		return array_map(function($assignment) {
			return new ilOrgUnitPosition($assignment->getPositionId());
		}, $assignments);
	}

	/**
	 * @param ilOrgUnitPosition[] $positions
	 * @return ilOrgUnitPosition[]
	 */
	protected function filterPositionByPermission(array $positions): array
	{
		$read_operation_id = $this->getOperationIdFor(self::PERMISSION_VIEW_LP);
		$edit_operation_id = $this->getOperationIdFor(self::PERMISSION_EDIT_LP);

		return $this->getPositionsWithSetOperation(
			$positions,
			array($read_operation_id, $edit_operation_id)
		);
	}

	/**
	 * @return int[]
	 */
	protected function getUserIdsByPositionAndUser(ilOrgUnitPosition $position, int $user_id): array
	{
		$ids = array();
		foreach ($position->getAuthorities() as $authority) {
			switch ($authority->getOver()) {
				case ilOrgUnitAuthority::OVER_EVERYONE:
					switch ($authority->getScope()) {
						case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
							$ids = array_merge(
								$ids,
								$this->org_unit_user_assignment->getUserIdsOfOrgUnitsOfUsersPosition(
									$position->getId(),
									$user_id
								)
							);
							break;
						case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
							$ids = array_merge(
								$ids,
								$this->org_unit_user_assignment->getUserIdsOfOrgUnitsOfUsersPosition(
									$position->getId(),
									$user_id, true
								)
							);
							break;
					}
					break;
				default:
					switch ($authority->getScope()) {
						case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
							$ids = array_merge(
								$ids,
								$this->org_unit_user_assignment->getUserIdsOfUsersOrgUnitsInPosition(
									$user_id,
									$position->getId(),
									$authority->getOver()
								)
							);
							break;
						case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
							$ids = array_merge(
								$ids,
								$this->org_unit_user_assignment->getUserIdsOfUsersOrgUnitsInPosition(
									$user_id,
									$position->getId(),
									$authority->getOver(),
									true
								)
							);
							break;
					}
			}
		}

		return $ids;
	}

	/**
	 * @return ilOrgUnitOperation[]
	 */
	protected function getAssignmentsOf(int $user_id): array
	{
		return $this->org_unit_user_assignment->getAssignmentsOfUserId($user_id);
	}

	protected function getOperationIdFor(string $operation): ilOrgUnitOperation
	{
		$context = ilOrgUnitOperationContext::where(array( 'context' => ilOrgUnitOperationContext::CONTEXT_IASS ))->first();
		$operation = array_shift(
			ilOrgUnitOperation::where(
				array(
					'operation_string' => $operation,
					'context_id' => $context->getId()
				)
			)->get()
		);

		return $operation;
	}

	/**
	 * @param ilOrgUnitPosition[] $positions
	 * @param ilOrgUnitOperation[] $operation_ids
	 * @return int[]
	 */
	protected function getPositionsWithSetOperation(array $positions, array $operations): array
	{
		$filtered_positions = array();
		foreach ($positions as $position) {
			$ilOrgUnitPermission = ilOrgUnitPermissionQueries::getSetForRefId(
				$this->ref_id,
				$position->getId()
			);

			foreach ($operations as $operation) {
				if ($ilOrgUnitPermission->isOperationIdSelected($operation->getOperationId())) {
					$filtered_positions[] = $position;
				}
			}
		}

		return array_unique($filtered_positions);
	}

	/**
	 * @param ilOrgUnitPosition[] $positions
	 * @param int $user_id
	 * @return int[]
	 */
	protected function getOrgUnitsByPositions(array $positions, int $user_id): array
	{
		$orgus = array();
		foreach ($positions as $position) {
			$orgus = array_merge(
				$orgus,
				$orgus = $this->org_unit_user_assignment->getOrgUnitIdsOfUsersPosition(
					$position->getId(),
					$user_id
				)
			);
		}

		return array_unique($orgus);
	}
}