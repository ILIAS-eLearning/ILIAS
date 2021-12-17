<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;


/**
 * Unit tests for class ilCopyWizardOptions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCopyWizard
 */
class ilConditionsTest extends TestCase
{
    protected $backupGlobals = false;

    protected Container $dic;


    public function testCondition() : void
    {
        $condition = new ilCondition(new ilConditionTrigger(1,2,'drei'), 'invalid');
        $obligatory_condition = $condition->withObligatory(true);
        $this->assertTrue($obligatory_condition->getObligatory());
        $this->assertNull($condition->getObligatory());
    }



    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }
}
