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

namespace ILIAS\GlobalScreen\GUI\Input;

use ILIAS\HTTP\Services;
use ILIAS\Refinery;
use ILIAS\Data\Factory;
use ILIAS\UI\URLBuilderToken;
use ILIAS\GlobalScreen\GUI\Flow\Flow;
use ILIAS\GlobalScreen\GUI\Hasher;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use ILIAS\GlobalScreen\GUI\PonsGUI;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Input
{
    use Hasher;

    /**
     * @var string
     */
    public const ALL_OBJECTS = 'ALL_OBJECTS';
    /**
     * @var string
     */
    private const INTERRUPTIVE_ITEMS = 'interruptive_items';

    public function __construct(
        private Services $http,
        private Refinery\Factory $refinery,
        private Factory $data_factory,
        private \ilLanguage $lng,
        private Flow $flow
    ) {
    }

    public function getFirstFromRequest(
        null|string|URLBuilderToken|TokenContainer $token
    ): string {
        if ($token === null) {
            return '';
        }

        if ($token instanceof TokenContainer) {
            $token = $token->token();
        }

        $query_params = $this->request()->getQueryParams(); // aka $_GET
        $name = $token instanceof URLBuilderToken ? $token->getName() : $token;
        $id = $query_params[$name] ?? ''; // field id

        if (is_array($id) && count($id) === 1 && isset($id[0]) && $id[0] === self::ALL_OBJECTS) {
            return self::ALL_OBJECTS; // special case for all objects
        }

        if (is_array($id)) {
            $id = $id[0] ?? null;
            return $id === null ? '' : $this->unhash($id);
        }

        return $this->unhash($id);
    }

    public function keepTokens(PonsGUI $gui): void
    {
        foreach ($gui->getTokensToKeep() as $token) {
            $this->keep($token);
        }
    }

    public function keep(
        null|string|URLBuilderToken|TokenContainer $token
    ): void {
        if ($token instanceof TokenContainer) {
            $token = $token->token();
        }
        $ctrl = $this->flow->ctrl();
        $current_class = $ctrl->getCmdClass();
        $name = $token instanceof URLBuilderToken ? $token->getName() : $token;
        $ctrl->setParameterByClass($current_class, $name, $this->hash($this->getFirstFromRequest($token)));
    }

    public function getAllFromRequest(
        null|string|URLBuilderToken|TokenContainer $token
    ): array {
        if ($token === null) {
            return [];
        }

        if ($token instanceof TokenContainer) {
            $token = $token->token();
        }

        $query_params = $this->request()->getQueryParams(); // aka $_GET
        $name = $token instanceof URLBuilderToken ? $token->getName() : $token;
        $ids = $query_params[$name] ?? []; // array of field ids
        $ids = is_array($ids) ? $ids : [$ids];

        // all objects
        if (($ids[0] ?? null) === self::ALL_OBJECTS) {
            return [self::ALL_OBJECTS]; // currently we cannot support all
        }

        // check interruptive items
        if (($interruptive_items = $this->http->request()->getParsedBody()[self::INTERRUPTIVE_ITEMS] ?? false)) {
            foreach ($interruptive_items as $interruptive_item) {
                $ids[] = $interruptive_item;
            }
        }
        $return_ids = [];
        foreach ($ids as $id) {
            try {
                $return_ids[] = $this->unhash($id);
            } catch (\Throwable $e) {
                // skip invalid ids
            }
        }
        return $return_ids;
    }

    public function buildToken(string $namespace, string $token, ?URI $uri = null): TokenContainer
    {
        if ($uri === null) {
            $uri = $this->flow->getHereAsURI();
        }

        $builder = new URLBuilder($uri);

        return new TokenContainer(...$builder->acquireParameter([$namespace], $token));
    }

    public function request(): ServerRequestInterface
    {
        return $this->http->request();
    }

    public function refinery(): Refinery\Factory
    {
        return $this->refinery;
    }

}
