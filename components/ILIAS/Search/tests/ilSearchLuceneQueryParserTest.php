<?php

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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Unit tests for class ilDidacticTemplate
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCopyWizard
 */
class ilSearchLuceneQueryParserTest extends TestCase
{
    /**
     * Inherited
     * @var bool
     */
    protected $backupGlobals = false;

    protected Container $dic;

    protected function setUp(): void
    {
        $this->initDependencies();
        parent::setUp();
    }

    public function testConstruct(): void
    {
        $qp = new ilLuceneQueryParser('query');
        $this->assertTrue($qp instanceof ilLuceneQueryParser);
    }

    public function testValidation(): void
    {
        $qp = new ilLuceneQueryParser('');
        $this->assertTrue(ilLuceneQueryParser::validateQuery('type:crs'));
    }

    public function testFailedParenthesis(): void
    {
        $this->expectException(ilLuceneQueryParserException::class);
        ilLuceneQueryParser::validateQuery('(()');
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies(): void
    {
    }
}
