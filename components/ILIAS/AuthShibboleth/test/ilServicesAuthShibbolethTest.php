<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilServicesAuthShibbolethTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
        $DIC['ilDB'] = $this->createMock(ilDBInterface::class);
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    public function testRuleAssignement(): void
    {
        $rule = new ilShibbolethRoleAssignmentRule();
        $rule->setName('attribute_1');
        $rule->setValue('value_1');

        $this->assertTrue($rule->matches(['attribute_1' => 'value_1']));
        $this->assertFalse($rule->matches(['attribute_2' => 'value_2']));
    }

    public function testWildcardRuleAssignement(): void
    {
        $rule = new ilShibbolethRoleAssignmentRule();
        $rule->setName('attribute_1');
        $rule->setValue('value_*');

        $this->assertTrue($rule->matches(['attribute_1' => 'value_1']));
        $this->assertTrue($rule->matches(['attribute_1' => 'value_2']));
        $this->assertFalse($rule->matches(['attribute_2' => 'value_2']));
    }
}
