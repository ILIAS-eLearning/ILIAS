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

namespace ILIAS\ApacheAuth\UsernameProvider;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves a username by selecting the first provider (by descending priority)
 * that returns a non-empty/non-null value for getUsername().
 */
final class UsernameResolver
{
    /** @var list<UsernameProvider> */
    private array $providers;

    /**
     * @param list<UsernameProvider> $providers
     */
    public function __construct(array $providers, private readonly \ilLogger $logger)
    {
        if (!array_is_list($providers)) {
            throw new \InvalidArgumentException('Providers must be passed as list');
        }

        $this->providers = $providers;

        // Sort by descending priority, this also ensures the expected type of the elements
        usort(
            $this->providers,
            static fn(UsernameProvider $a, UsernameProvider $b): int => $b->getPriority() <=> $a->getPriority()
        );
    }

    public function resolve(ServerRequestInterface $request): UsernameInterface
    {
        foreach ($this->providers as $provider) {
            $this->logger->debug('Trying to resolve username using provider {provider} with prio {priority}', [
                'provider' => $provider::class,
                'priority' => $provider->getPriority(),
            ]);

            $username = $provider->getUsername($request);

            if (!$username->isEmpty()) {
                $this->logger->debug('Username resolved to {username} by provider {provider}', [
                    'username' => $username->asString(),
                    'provider' => $provider::class,
                ]);

                return $username;
            }

            $this->logger->debug('Provider {provider} could not resolve a username', [
                'provider' => $provider::class,
            ]);
        }

        $this->logger->debug('No username could be resolved by any provider, returning NullUsername');

        return new NullUsername();
    }
}
