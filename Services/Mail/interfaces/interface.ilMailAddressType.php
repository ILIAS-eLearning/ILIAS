<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailAddressType
{
    /**
     * Returns an array of resolved user ids based on an address instance.
     * @return int[]
     */
    public function resolve() : array;

    /**
     * Validates the parsed recipients and set errors accordingly.
     * @param $senderId integer The id of the acting ILIAS user, can be used for permission checks etc.
     * @return bool
     * @see ilMailAddressType::getErrors
     */
    public function validate(int $senderId) : bool;

    /**
     * Returns a list of errors determined in the validation process. The errors should be reset everytime the
     * validation is triggered.
     * @return ilMailError[]
     * @see ilMailAddressType::validate
     */
    public function getErrors() : array;

    /**
     * The address instance used for validation and user id lookup.
     * @return ilMailAddress
     */
    public function getAddress() : ilMailAddress;
}