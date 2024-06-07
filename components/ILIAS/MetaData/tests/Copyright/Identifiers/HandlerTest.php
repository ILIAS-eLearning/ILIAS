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

namespace ILIAS\MetaData\Copyright\Identifiers;

use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    protected function getHandler(): Handler
    {
        return new class () extends Handler {
            protected function getInstID(): string
            {
                return '1234';
            }
        };
    }

    public function testbuildIdentifierFromEntryID(): void
    {
        $handler = $this->getHandler();

        $identifier = $handler->buildIdentifierFromEntryID(32);

        $this->assertSame(
            'il_copyright_entry__1234__32',
            $identifier
        );
    }

    public function testIsIdentifierValidTrue(): void
    {
        $handler = $this->getHandler();

        $this->assertTrue(
            $handler->isIdentifierValid('il_copyright_entry__1234__32')
        );
    }

    public function testIsIdentifierValidFalseWrongFormat(): void
    {
        $handler = $this->getHandler();

        $this->assertFalse(
            $handler->isIdentifierValid('invalid string')
        );
    }

    public function testIsIdentifierValidFalseWrongInstID(): void
    {
        $handler = $this->getHandler();

        $this->assertFalse(
            $handler->isIdentifierValid('il_copyright_entry__999__32')
        );
    }

    public function testParseEntryIDFromIdentifier(): void
    {
        $handler = $this->getHandler();

        $entry_id = $handler->parseEntryIDFromIdentifier('il_copyright_entry__1234__32');

        $this->assertSame(32, $entry_id);
    }

    public function testParseEntryIDFromIdentifierWrongFormat(): void
    {
        $handler = $this->getHandler();

        $entry_id = $handler->parseEntryIDFromIdentifier('invalid string');

        $this->assertSame(0, $entry_id);
    }

    public function testParseEntryIDFromIdentifierWrongInstID(): void
    {
        $handler = $this->getHandler();

        $entry_id = $handler->parseEntryIDFromIdentifier('il_copyright_entry__999__32');

        $this->assertSame(0, $entry_id);
    }
}
