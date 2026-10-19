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

namespace ILIAS\Language\Activities;

use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Component\Activities\ActivityImpl;
use ILIAS\Component\Activities\ActivityType;
use ILIAS\Data\Result;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\LanguageFolder;
use ILIAS\UI\Component\Input\Factory as InputFactory;

abstract class LanguageActivity extends ActivityImpl
{
    use GrindsFormInput;

    /** @param RBAC|null $rbac null only for forSetup(); isAllowedToPerform() then throws. */
    public function __construct(
        private readonly ?RBAC $rbac,
        private readonly LanguageFolder $language_folder,
        protected readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getType(): ActivityType
    {
        return ActivityType::Command;
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        if ($this->rbac === null) {
            throw new \LogicException(
                'RBAC is not available during Setup; '
                . static::class . '::isAllowedToPerform() cannot be used here.'
            );
        }

        if (!$this->isKnownUser($usr_id)) {
            return false;
        }

        return $this->rbac->system()->checkAccessOfUser(
            $usr_id,
            'write',
            $this->language_folder->getRefId()
        );
    }

    /** Never the anonymous user, regardless of its RBAC roles. */
    private function isKnownUser(int $usr_id): bool
    {
        if ($usr_id <= 0) {
            return false;
        }

        return !defined('ANONYMOUS_USER_ID') || $usr_id !== ANONYMOUS_USER_ID;
    }

    public function maybePerformAs(InputFactory $input_factory, int $usr_id, array $raw_parameters): Result
    {
        try {
            // inside the try: getInputDescription() may throw too; value() throws grind's input error
            $parameters = $this->normalizeParameters(
                $this->grind($this->getInputDescription($input_factory->field()), $raw_parameters)->value()
            );
            if (!$this->isAllowedToPerform($usr_id, $parameters)) {
                return new Result\Error(
                    new \DomainException('You are not allowed to manage the languages of this installation.', 403)
                );
            }

            // usr_id last: the trusted value wins over a spoofed form key
            return new Result\Ok($this->perform([...$parameters, 'usr_id' => $usr_id]));
        } catch (\Throwable $e) {
            return new Result\Error(Failure::of($e));
        }
    }

    /** @return array<string, mixed> */
    abstract protected function normalizeParameters(array $grind_result): array;
}
