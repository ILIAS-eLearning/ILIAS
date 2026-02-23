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

namespace ILIAS\StaticURL\Shortlinks;

use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\AliasedHandler;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\URI;
use ILIAS\StaticURL\Shortlinks\Shortlink\RepositoryDB;
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\ILIASTypeDataResolver;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Handler implements AliasedHandler
{
    public const string SHORTLINK_NAMESPACE = self::NAMESPACE_ALTERNATIVE;
    private const string NAMESPACE_SHORT = 's';
    private const string NAMESPACE_LONG = 'shortlink';
    private const string NAMESPACE_ALTERNATIVE = 'to';

    public function __construct()
    {
    }

    public function getNamespace(): string
    {
        return self::SHORTLINK_NAMESPACE;
    }

    public function getNamespaceAliasses(): array
    {
        return array_diff(
            [self::NAMESPACE_LONG, self::NAMESPACE_ALTERNATIVE, self::NAMESPACE_SHORT],
            [$this->getNamespace()]
        );
    }

    public function canHandle(Request $request): bool
    {
        if ($request->getNamespace() === $this->getNamespace()) {
            return true;
        }
        return in_array(
            $request->getNamespace(),
            $this->getNamespaceAliasses()
        );
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        global $DIC;
        $repository = new RepositoryDB(
            $DIC->database(),
            new ILIASTypeDataResolver(
                $DIC->repositoryTree()
            )
        );
        $alias = $request->getAdditionalParameters()[0] ?? '';
        if (!$repository->has($alias)) {
            return $response_factory->cannot();
        }

        if (($shortlink = $repository->getByAlias($alias)) === null) {
            return $response_factory->cannot();
        };

        if (!$shortlink->isActive()) {
            return $response_factory->cannot();
        }

        $target_resolver = new TargetLinkResolver(
            $DIC['static_url']->builder(),
            $DIC[\ILIAS\Data\Factory::class]
        );

        $target = $target_resolver->resolve(
            $shortlink
        );
        if ($target instanceof URI) {
            $target = $target->getPath();
        }

        if (empty($target)) {
            return $response_factory->cannot();
        }

        $repository->increaseUsage(
            $shortlink
        );

        return $response_factory->can(
            $target,
            $this->mustShift($context->http()->request())
        );
    }

    protected function mustShift(ServerRequestInterface $request): bool
    {
        $requested_uri = $request->getUri()->getPath();

        return !str_contains($requested_uri, StandardURIBuilder::SHORT)
            && !str_contains($requested_uri, StandardURIBuilder::LONG);
    }

}
