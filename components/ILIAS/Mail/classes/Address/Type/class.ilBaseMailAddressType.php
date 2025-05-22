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

abstract class ilBaseMailAddressType implements ilMailAddressType
{
    /** @var list<ilMailError> */
    private array $errors = [];

    public function __construct(
        protected ilMailAddressTypeHelper $type_helper,
        protected ilMailAddress $address,
        protected ilLogger $logger
    ) {
    }

    abstract protected function isValid(int $sender_id): bool;

    public function validate(int $sender_id): bool
    {
        $this->resetErrors();

        return $this->isValid($sender_id);
    }

    /**
     * @param list<string> $place_holder_values
     */
    protected function pushError(string $language_variable, array $place_holder_values = []): void
    {
        $this->errors[] = new ilMailError($language_variable, $place_holder_values);
    }

    private function resetErrors(): void
    {
        $this->errors = [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getAddress(): ilMailAddress
    {
        return $this->address;
    }
}
