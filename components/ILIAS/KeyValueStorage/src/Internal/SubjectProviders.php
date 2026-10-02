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

namespace ILIAS\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\Subject\SubjectProvider;

/**
 * The contributed subject providers, keyed by their name. Unique names are
 * checked by the build only, see {@see \ILIAS\KeyValueStorage\Setup\SubjectProviderNamesUniqueObjective}.
 *
 * @internal
 */
final readonly class SubjectProviders
{
    /** @var array<string, class-string<SubjectProvider>> */
    private array $providers;

    /**
     * @param iterable<SubjectProvider> $providers
     */
    public function __construct(iterable $providers)
    {
        $by_name = [];
        foreach ($providers as $provider) {
            if (!$provider instanceof SubjectProvider) {
                throw new \InvalidArgumentException('Expected a subject provider.');
            }
            $by_name[$provider->name()] = $provider::class;
        }
        $this->providers = $by_name;
    }

    /**
     * @throws \InvalidArgumentException if the subject was not named by the contributed provider of its name
     */
    public function assertRegistered(SubjectId $subject): void
    {
        $registered = $this->providers[$subject->provider()] ?? null;
        if ($registered === null) {
            throw new \InvalidArgumentException(
                'The subject provider "' . $subject->provider() . '" is not registered, '
                . 'contribute it via $contribute[' . SubjectProvider::class . '::class].'
            );
        }
        if ($registered !== $subject->providerClass()) {
            throw new \InvalidArgumentException(
                'The subject provider "' . $subject->provider() . '" is registered as ' . $registered
                . ', not as ' . $subject->providerClass() . '.'
            );
        }
    }
}
