<?php

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

    public function testSetGetTitle()
    {
        $sk = $this->skill_category;
        $sk->setTitle("title");

        $this->assertEquals(
            "title",
            $sk->getTitle()
        );
    }
}
