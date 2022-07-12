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
     * @param int $senderId The id of the acting ILIAS user, can be used for permission checks etc.
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
     */
    public function getAddress() : ilMailAddress;
}
