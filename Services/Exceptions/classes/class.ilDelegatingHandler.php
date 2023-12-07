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

use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;

/**
 * A Whoops error handler that delegates calls on it self to another handler that is created only in the
 * case an error is thrown. This is necessary to make it possible to use another handler when the DEVMODE
 * is activated.
 * During the Init-Dance (see ilInitialisation), the error handling is initialized before the client ini file
 * is read and the DEVMODE is determined. Thus we can't initialize a handler based on the DEVMODE and need this
 * workaround.
 * This class is not ment to be extended, as the definition of error handlers should be handled in one place
 * in ilErrorHandling, so this class acts rather dump and asks ilErrorHandling for a handler.
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
final class ilDelegatingHandler extends Handler
{
    private ilErrorHandling $error_handling;
    private ?HandlerInterface $current_handler = null;

    public function __construct(ilErrorHandling $error_handling)
    {
        $this->error_handling = $error_handling;
    }

    private function hideSensitiveData(array $key_value_pairs): array
    {
        foreach ($key_value_pairs as $key => &$value) {
            if (is_array($value)) {
                $value = $this->hideSensitiveData($value);
            }

            if ($key === 'password' && is_string($value)) {
                $value = 'REMOVED FOR SECURITY';
            }

            if ($key === 'PHPSESSID' && is_string($value)) {
                $value = substr($value, 0, 5) . ' (SHORTENED FOR SECURITY)';
            }

            if ($key === 'HTTP_COOKIE') {
                $cookie_content = explode(';', $value);
                foreach ($cookie_content as &$cookie_pair_string) {
                    $cookie_pair = explode('=', $cookie_pair_string);
                    if (trim($cookie_pair[0]) === session_name()) {
                        $cookie_pair[1] = substr($cookie_pair[1], 0, 5) . ' (SHORTENED FOR SECURITY)';
                        $cookie_pair_string = implode('=', $cookie_pair);
                    }
                }
                $value = implode(';', $cookie_content);
            }
        }

        return $key_value_pairs;
    }

    /**
     * Last missing method from HandlerInterface.
     * Asks ilErrorHandling for the appropriate Handler and delegates it's tasks to
     * that handler.
     * @inheritDoc
     * @noinspection PhpCastIsUnnecessaryInspection
     */
    public function handle(): ?int
    {
        if (defined("IL_INITIAL_WD")) {
            chdir(IL_INITIAL_WD);
        }

        /* We must cast the superglobals back to normal arrays since the error handler needs them. They were replaced by
           SuperGlobalDropInReplacement . The keys contain NULL bytes, so accessing values directly by key is not
           really possible */
        $_GET = $this->hideSensitiveData((array) $_GET);
        $_POST = $this->hideSensitiveData((array) $_POST);
        $_COOKIE = $this->hideSensitiveData((array) $_COOKIE);
        $_REQUEST = $this->hideSensitiveData((array) $_REQUEST);

        $_SERVER = $this->hideSensitiveData($_SERVER);

        $this->current_handler = $this->error_handling->getHandler();
        $this->current_handler->setRun($this->getRun());
        $this->current_handler->setException($this->getException());
        $this->current_handler->setInspector($this->getInspector());
        return $this->current_handler->handle();
    }

    /**
     * This is an implicit interface method of the Whoops handlers
     * @see: \Whoops\Run::handleException
     */
    public function contentType(): ?string
    {
        if ($this->current_handler === null ||
            !method_exists($this->current_handler, 'contentType')) {
            return null;
        }

        return $this->current_handler->contentType();
    }
}
