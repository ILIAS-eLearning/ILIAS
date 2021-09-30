<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBaseMailAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailAddressType implements ilMailAddressType
{
    protected ilMailAddressTypeHelper $typeHelper;
    protected ilMailAddress $address;
    protected ilLogger $logger;
    /** @var ilMailError[] */
    private array $errors = [];

    public function __construct(
        ilMailAddressTypeHelper $typeHelper,
        ilMailAddress $address,
        ilLogger $logger
    ) {
        $this->address = $address;
        $this->typeHelper = $typeHelper;
        $this->logger = $logger;
    }

    abstract protected function isValid(int $senderId) : bool;

    public function validate(int $senderId) : bool
    {
        $this->resetErrors();

        return $this->isValid($senderId);
    }

    protected function pushError(string $languageVariable, array $placeHolderValues = []) : void
    {
        $this->errors[] = new ilMailError($languageVariable, $placeHolderValues);
    }

    private function resetErrors() : void
    {
        $this->errors = [];
    }

    public function getErrors() : array
    {
        return $this->errors;
    }

    public function getAddress() : ilMailAddress
    {
        return $this->address;
    }
}
