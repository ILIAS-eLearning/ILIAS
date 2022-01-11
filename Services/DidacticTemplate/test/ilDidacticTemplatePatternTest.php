<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;


/**
 * Unit tests for class ilDidacticTemplate
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCopyWizard
 */
class ilDidacticTemplatePatternTest extends TestCase
{
    protected $backupGlobals = false;

    protected Container $dic;

    protected function setUp() : void
    {
        $this->initPatternDependencies();
        parent::setUp();
    }

    public function testConstruct() : void
    {
        $include_pattern = new ilDidacticTemplateIncludeFilterPattern();
        $this->assertTrue($include_pattern instanceof ilDidacticTemplateIncludeFilterPattern);

        $exclude_pattern = new ilDidacticTemplateExcludeFilterPattern();
        $this->assertTrue($exclude_pattern instanceof ilDidacticTemplateExcludeFilterPattern);
    }

    public function testMatches() : void
    {
        $include_pattern = new ilDidacticTemplateIncludeFilterPattern();
        $include_pattern->setPatternSubType(ilDidacticTemplateIncludeFilterPattern::PATTERN_SUBTYPE_REGEX);
        $include_pattern->setPattern('^il_crs_admin_[0-9]+$');
        $this->assertTrue($include_pattern->valid('il_crs_admin_123'));

        $exclude_pattern = new ilDidacticTemplateExcludeFilterPattern();
        $exclude_pattern->setPatternSubType(ilDidacticTemplateIncludeFilterPattern::PATTERN_SUBTYPE_REGEX);
        $exclude_pattern->setPattern('il_crs_admin_[0-9]+');
        $this->assertTrue($exclude_pattern->valid('il_grp_admin'));
    }

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initPatternDependencies() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));

        $logger = $this->getMockBuilder(ilLogger::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $logger_factory = $this->getMockBuilder(ilLoggerFactory::class)
                               ->disableOriginalConstructor()
                               ->onlyMethods(['getComponentLogger'])
                               ->getMock();
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
    }
}
