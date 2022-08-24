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
 * Class ilMailAddressListTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressListTest extends ilMailBaseTest
{
    public function addressTestProvider(): array
    {
        return [
            'Username Addresses' => [
                [
                    new ilMailAddress('phpunit', 'ilias'),
                ],
                [
                    new ilMailAddress('user', 'ilias'),
                    new ilMailAddress('max.mustermann', 'ilias.de'),
                ],
                1,
            ],
            'Role Addresses' => [
                [
                    new ilMailAddress('#il_ml_4711', 'ilias'),
                    new ilMailAddress('#il_ml_4712', 'ilias'),
                    new ilMailAddress('#il_ml_4713', 'ilias'),
                ],
                [
                    new ilMailAddress('#il_ml_4713', 'ilias'),
                    new ilMailAddress('#il_role_1000', 'ilias'),
                    new ilMailAddress('#admin', '[Math Course]'),
                ],
                2,
            ],
        ];
    }

    /**
     * @dataProvider addressTestProvider
     */
    public function testDiffAddressListCanCalculateTheDifferenceOfTwoLists(
        array $leftAddresses,
        array $rightAddresses,
        int $numberOfExpectedItems
    ): void {
        $left = new ilMailAddressListImpl($leftAddresses);
        $right = new ilMailAddressListImpl($rightAddresses);

        $list = new ilMailDiffAddressList($left, $right);
        $this->assertCount($numberOfExpectedItems, $list->value());
    }

    public function externalAddressTestProvider(): array
    {
        return [
            'Username' => [
                new ilMailAddress('user', 'ilias'),
                0
            ],
            'Email Address exists as Username' => [
                new ilMailAddress('max.mustermann', 'ilias.de'),
                0
            ],
            'Email Address' => [
                new ilMailAddress('phpunit', 'gmail.com'),
                1
            ],
            'Mailing List' => [
                new ilMailAddress('#il_ml_4713', 'ilias'),
                0
            ],
            'Role (technical)' => [
                new ilMailAddress('#il_role_1000', 'ilias'),
                0
            ],
            'Role (human readable)' => [
                new ilMailAddress('#admin', '[Math Course]'),
                0
            ],
        ];
    }

    /**
     * @dataProvider externalAddressTestProvider
     * @param ilMailAddress $address
     * @param int $numberOfExpectedItems
     */
    public function testExternalAddressListDecoratorFiltersExternalAddresses(
        ilMailAddress $address,
        int $numberOfExpectedItems
    ): void {
        $list = new ilMailAddressListImpl([$address]);
        $externalList = new ilMailOnlyExternalAddressList($list, 'ilias', static function (string $address): int {
            if ('max.mustermann@ilias.de' === $address) {
                return 4711;
            }

            return 0;
        });

        $this->assertCount($numberOfExpectedItems, $externalList->value());
    }
}
