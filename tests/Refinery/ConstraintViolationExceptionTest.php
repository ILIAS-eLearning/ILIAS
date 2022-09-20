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

namespace ILIAS\Tests\Refinery;

use ILIAS\Refinery\ConstraintViolationException;

class ConstraintViolationExceptionTest extends TestCase
{
    public function testTranslationOfMessage(): void
    {
        $callback = function (string $languageId): string {
            $this->assertEquals('some_key', $languageId);
            return 'Some text "%s" and "%s"';
        };

        try {
            throw new ConstraintViolationException(
                'This is an error message for developers',
                'some_key',
                'Value To Replace',
                'Some important stuff'
            );
        } catch (ConstraintViolationException $exception) {
            $this->assertEquals(
                'Some text "Value To Replace" and "Some important stuff"',
                $exception->getTranslatedMessage($callback)
            );

            $this->assertEquals(
                'This is an error message for developers',
                $exception->getMessage()
            );
        }
    }
}
