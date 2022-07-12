<?php declare(strict_types=1);

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
