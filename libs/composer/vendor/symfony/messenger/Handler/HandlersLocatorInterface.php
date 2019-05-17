<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Handler;

use Symfony\Component\Messenger\Envelope;

/**
 * Maps a message to a list of handlers.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @experimental in 4.2
 */
interface HandlersLocatorInterface
{
    /**
     * Returns the handlers for the given message name.
     *
     * @return iterable|callable[] Indexed by handler alias if available
     */
    public function getHandlers(Envelope $envelope): iterable;
}
