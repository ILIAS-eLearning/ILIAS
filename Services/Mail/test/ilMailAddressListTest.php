<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressListTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressListTest extends \ilMailBaseTest
{
    /**
     * @return array
     */
    public function addressProvider() : array
    {
        return [
            [
                [
                    new \ilMailAddress('phpunit', 'ilias'),
                ],
                [
                    new \ilMailAddress('user', 'ilias'),
                    new \ilMailAddress('max.mustermann', 'ilias.de')
                ],
                1
            ],
            [
                [
                    new \ilMailAddress('#il_ml_4711', 'ilias'),
                    new \ilMailAddress('#il_ml_4712', 'ilias'),
                    new \ilMailAddress('#il_ml_4713', 'ilias'),
                ],
                [
                    new \ilMailAddress('#il_ml_4713', 'ilias'),
                    new \ilMailAddress('#il_role_1000', 'ilias'),
                    new \ilMailAddress('#admin', '[Math Course]')
                ],
                2
            ]
        ];
    }

    /**
     * @param array $leftAddresses
     * @param array $rightAddresses
     * @param int $numberOfExpectedItems
     * @dataProvider addressProvider
     */
    public function testDiffAddressListCanCalculateTheDifferenceOfTwoLists(
        array $leftAddresses,
        array $rightAddresses,
        int $numberOfExpectedItems
    ) {
        $left = new \ilMailAddressListImpl($leftAddresses);
        $right = new \ilMailAddressListImpl($rightAddresses);

        $list = new \ilMailDiffAddressList($left, $right);
        $this->assertCount($numberOfExpectedItems, $list->value());
    }
}
