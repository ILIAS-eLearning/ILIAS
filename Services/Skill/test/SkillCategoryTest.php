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
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class ilSkillCategory
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillCategoryTest extends TestCase
{
    protected ilSkillCategory $skill_category;

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }


    protected function setUp() : void
    {
        parent::setUp();

        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        $db = $this->createMock(ilDBInterface::class);
        $this->setGlobalVariable(
            "ilDB",
            $db
        );

        $this->skill_category = new ilSkillCategory();
    }

    protected function tearDown() : void
    {
    }

    public function testProperties() : void
    {
        $sk = $this->skill_category;
        $sk->setTitle("A skill category");
        $sk->setDescription("This is a description for a skill category.");
        $sk->setSelfEvaluation(true);
        $sk->setOrderNr(10);
        $sk->setStatus(0);
        $sk->setId(4);
        $sk->setImportId("an_import_id");

        $this->assertEquals(
            "scat",
            $sk->getType()
        );
        $this->assertEquals(
            "A skill category",
            $sk->getTitle()
        );
        $this->assertEquals(
            "This is a description for a skill category.",
            $sk->getDescription()
        );
        $this->assertEquals(
            true,
            $sk->getSelfEvaluation()
        );
        $this->assertEquals(
            10,
            $sk->getOrderNr()
        );
        $this->assertEquals(
            0,
            $sk->getStatus()
        );
        $this->assertEquals(
            4,
            $sk->getId()
        );
        $this->assertEquals(
            "an_import_id",
            $sk->getImportId()
        );
    }
}
