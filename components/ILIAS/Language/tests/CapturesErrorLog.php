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

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/** PHPUnit captures error_log() only after expectErrorLog(), otherwise it goes to stderr: log to a file instead. */
trait CapturesErrorLog
{
    private ?string $captured_error_log_file = null;
    private string $error_log_ini_before_test = '';
    private bool $captured_error_log_was_read = false;

    #[Before]
    protected function redirectErrorLogToFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'lngerrlog');
        $this->assertNotFalse($file);
        $this->captured_error_log_file = $file;
        $this->captured_error_log_was_read = false;
        $this->error_log_ini_before_test = (string) ini_get('error_log');
        ini_set('error_log', $file);
    }

    /** Whatever was logged and not asserted on is a failure, as unexpected output was before. */
    #[After]
    protected function restoreErrorLog(): void
    {
        $file = $this->captured_error_log_file;
        if ($file === null) {
            return;
        }
        $this->captured_error_log_file = null;
        try {
            $unexpected = $this->captured_error_log_was_read || !is_file($file) ? '' : (string) file_get_contents($file);
        } finally {
            ini_set('error_log', $this->error_log_ini_before_test);
            @unlink($file);
        }
        if ($unexpected !== '') {
            $this->fail('Unexpected error_log(): ' . $unexpected);
        }
    }

    protected function loggedErrors(): string
    {
        $this->captured_error_log_was_read = true;

        return $this->captured_error_log_file !== null && is_file($this->captured_error_log_file)
            ? (string) file_get_contents($this->captured_error_log_file)
            : '';
    }

    protected function assertLoggedErrorMatches(string $pattern): void
    {
        $this->assertMatchesRegularExpression($pattern, $this->loggedErrors());
    }

    protected function assertNothingLogged(): void
    {
        $this->assertSame('', $this->loggedErrors());
    }
}
