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

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class UploadPolicyResolver
{
    /**
     * Multiply for MB to Bytes, divide for Bytes to MB.
     */
    protected const MB_TO_BYTES = 1_000 * 1_000;

    /**
     * @param UploadPolicy[] $upload_policies
     */
    public function __construct(
        protected ilRbacReview $rbac_review,
        protected ilObjUser $current_user,
        protected array $upload_policies,
    ) {
    }

    /**
     * Returns the upload-size limit of the current user if customized by any upload policy.
     * If multiple policies apply, the largest limit will be returned.
     */
    public function getUserUploadSizeLimitInBytes(): ?int
    {
        $largest_limit_in_bytes = null;
        foreach ($this->upload_policies as $policy) {
            if (!$this->isPolicyActiveAndValid($policy)) {
                continue;
            }

            $policy_limit_in_bytes = ($policy->getUploadLimitInMB() * self::MB_TO_BYTES);

            if (UploadPolicy::AUDIENCE_TYPE_ALL_USERS === $policy->getAudienceType() &&
                (null === $largest_limit_in_bytes || $policy_limit_in_bytes > $largest_limit_in_bytes)
            ) {
                $largest_limit_in_bytes = $policy_limit_in_bytes;
                continue;
            }

            if (UploadPolicy::AUDIENCE_TYPE_GLOBAL_ROLE === $policy->getAudienceType() &&
                $this->rbac_review->isAssignedToAtLeastOneGivenRole(
                    $this->current_user->getId(),
                    array_map(
                        'intval',
                        array_merge(
                            $policy->getAudience()['global_roles'] ?? [],
                            $policy->getAudience()['local_roles'] ?? []
                        )
                    )
                ) &&
                (null === $largest_limit_in_bytes || $policy_limit_in_bytes > $largest_limit_in_bytes)
            ) {
                $largest_limit_in_bytes = $policy_limit_in_bytes;
            }
        }

        return $largest_limit_in_bytes;
    }

    protected function isPolicyActiveAndValid(UploadPolicy $policy): bool
    {
        $valid_from = $policy->getValidFrom();
        $valid_until = $policy->getValidUntil();

        if (null === $valid_from && null === $valid_until) {
            return $policy->isActive();
        }

        $today = new \DateTimeImmutable('today midnight');

        if (null !== $valid_from && null !== $valid_until) {
            return $policy->isActive() && $valid_from >= $today && $today < $valid_until;
        }

        if (null !== $valid_until && null === $valid_from) {
            return $policy->isActive() && $today <= $valid_until;
        }

        if (null !== $valid_from && null === $valid_until) {
            return $policy->isActive() && $today >= $valid_from;
        }

        return false;
    }
}
