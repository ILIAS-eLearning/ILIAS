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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

abstract class ilCertificateBaseTestCase extends TestCase
{
    protected ?Container $dic;

    protected function setUp(): void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    /**
     * @param callable(): void $cb
     */
    protected function assertDoesNotThrow(callable $cb, string $message = ''): void
    {
        try {
            $cb();
            $this->addToAssertionCount(1);
        } catch (Throwable $e) {
            $this->fail(
                trim($message . ' ' . sprintf(
                    '(unexpected %s: %s)' . PHP_EOL . '%s',
                    get_class($e),
                    $e->getMessage(),
                    $e->getTraceAsString()
                ))
            );
        }
    }

    /**
     * @template T of Throwable
     * @param callable             $cb
     * @param class-string<T>|null $expected_class
     * @param string|null          $expected_message
     */
    protected function assertThrows(
        callable $cb,
        ?string $expected_class = null,
        ?string $expected_message = null
    ): void {
        try {
            $cb();
            $this->fail(sprintf(
                'Failed asserting that exception %s was thrown.',
                $expected_class ?? '(any exception)'
            ));
        } catch (Throwable $e) {
            if ($expected_class !== null && !$e instanceof $expected_class) {
                $this->fail(sprintf(
                    'Failed asserting exception of type %s. Got %s instead.',
                    $expected_class,
                    get_class($e)
                ));
            }
            if ($expected_message !== null && !str_contains($e->getMessage(), $expected_message)) {
                $this->fail(sprintf(
                    'Failed asserting exception message contains "%s". Actual message: "%s"',
                    $expected_message,
                    $e->getMessage()
                ));
            }
            $this->addToAssertionCount(1);
        }
    }

    protected function setGlobalVariable(string $name, mixed $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($name) {
            return $GLOBALS[$name];
        };
    }
}
