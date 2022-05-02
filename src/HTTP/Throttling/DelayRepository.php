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

namespace ILIAS\HTTP\Throttling;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DelayRepository
{
    protected const SESISION_KEY_PREFIX = 'http_delay_';

    public function get(string $identifier) : ?Delay
    {
        $identifier = $this->getSessionId($identifier);
        if (!\ilSession::has($identifier)) {
            return null;
        }

        $delay = \ilSession::get($identifier);
        if (null === $delay) {
            return null;
        }

        return unserialize(\ilSession::get($identifier), [Delay::class]);
    }

    public function set(Delay $delay, string $identifier) : void
    {
        $identifier = $this->getSessionId($identifier);
        \ilSession::set($identifier, serialize($delay));
    }

    public function remove(string $identifier) : void
    {
        $identifier = $this->getSessionId($identifier);
        if (\ilSession::has($identifier)) {
            \ilSession::set($identifier, null);
        }
    }

    protected function getSessionId(string $identifier) : string
    {
        return self::SESISION_KEY_PREFIX . $identifier;
    }
}
