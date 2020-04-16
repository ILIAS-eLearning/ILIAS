<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBaseMailAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailAddressType implements \ilMailAddressType
{
    /** @var \ilMailAddressTypeHelper */
    protected $typeHelper;

    /** @var \ilMailAddress */
    protected $address;

    /** @var \ilLogger */
    protected $logger;

    /** @var \ilMailError[] */
    private $errors = [];

    /**
     * ilBaseMailAddressType constructor.
     * @param \ilMailAddressTypeHelper $typeHelper
     * @param \ilMailAddress           $address
     * @param \ilLogger                 $logger
     */
    public function __construct(
        \ilMailAddressTypeHelper $typeHelper,
        \ilMailAddress $address,
        \ilLogger $logger
    ) {
        $this->address = $address;
        $this->typeHelper = $typeHelper;
        $this->logger = $logger;
    }

    /**
     * @param $senderId integer
     * @return bool
     */
    abstract protected function isValid(int $senderId) : bool;

    /**
     * @inheritdoc
     */
    public function validate(int $senderId) : bool
    {
        $this->resetErrors();

        return $this->isValid($senderId);
    }

    /**
     * @param string $languageVariable
     * @param array $placeHolderValues
     */
    protected function pushError(string $languageVariable, array $placeHolderValues = [])
    {
        $this->errors[] = new \ilMailError($languageVariable, $placeHolderValues);
    }

    /**
     *
     */
    private function resetErrors()
    {
        $this->errors = [];
    }

    /**
     * @inheritdoc
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     */
    public function getAddress() : \ilMailAddress
    {
        return $this->address;
    }
}
