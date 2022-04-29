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

use ILIAS\HTTP\Throttling\Delay\DelayInterface;
use ILIAS\HTTP\Throttling\Delay\NullDelay;
use ILIAS\HTTP\Throttling\Delay\Delay;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class DelayRepository
{
    protected const SESISION_KEY_PREFIX = 'http_delay_';

    public function get(string $identifier) : DelayInterface
    {
        $identifier = $this->getSessionId($identifier);
        if (\ilSession::has($identifier)) {
            return unserialize(\ilSession::get($identifier), [Delay::class]);
        }

        return new NullDelay();
    }

    public function set(DelayInterface $delay, string $identifier) : void
    {
        $identifier = $this->getSessionId($identifier);
        \ilSession::set($identifier, serialize($delay));
    }

    public function remove(string $identifier) : void
    {
        $identifier = $this->getSessionId($identifier);
        if (\ilSession::has($identifier)) {
            \ilSession::set($identifier, serialize(new NullDelay()));
        }
    }

    protected function getSessionId(string $identifier) : string
    {
        return self::SESISION_KEY_PREFIX . $identifier;
    }
}
