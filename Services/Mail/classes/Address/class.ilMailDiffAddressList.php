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
 * Class ilMailDiffAddressList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDiffAddressList implements ilMailAddressList
{
    protected ilMailAddressList $left;
    protected ilMailAddressList $right;

    public function __construct(ilMailAddressList $left, ilMailAddressList $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public function value(): array
    {
        $leftAddresses = $this->left->value();
        $rightAddresses = $this->right->value();

        return array_diff($leftAddresses, $rightAddresses);
    }
}
