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

namespace ILIAS\StaticURL;

use ILIAS\AccessControl\PublicInterface\Access;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\StaticURL\Legacy\CtrlProxy;
use ILIAS\StaticURL\Legacy\LanguageProxy;
use ILIAS\StaticURL\Legacy\MainTemplateProxy;
use ILIAS\StaticURL\Legacy\RepositoryTreeProxy;
use ILIAS\StaticURL\Legacy\SettingsProxy;
use ILIAS\StaticURL\Legacy\UserProxy;

/**
 * What a {@see \ILIAS\StaticURL\Handler\Handler} may reach out to while it
 * resolves a static URL.
 *
 * Services that are wired through the component bootstrap are injected as
 * themselves. Everything else still lives in the legacy container and is named
 * by a dedicated proxy, so this class states its dependencies instead of
 * holding a container that can produce anything.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Context
{
    public function __construct(
        private readonly GlobalHttpState $http,
        private readonly Factory $refinery,
        private readonly Access $access,
        private readonly URIBuilder $builder,
        private readonly UserProxy $user,
        private readonly RepositoryTreeProxy $tree,
        private readonly LanguageProxy $language,
        private readonly MainTemplateProxy $main_template,
        private readonly CtrlProxy $ctrl,
        private readonly SettingsProxy $settings,
    ) {
    }

    public function getUserLanguage(): string
    {
        return $this->user->getCurrentLanguage();
    }

    public function refinery(): Factory
    {
        return $this->refinery;
    }

    public function lng(): \ilLanguage
    {
        return $this->language->get();
    }

    public function mainTemplate(): \ilGlobalTemplateInterface
    {
        return $this->main_template->get();
    }

    public function http(): GlobalHttpState
    {
        return $this->http;
    }

    public function ctrl(): \ilCtrlInterface
    {
        return $this->ctrl->get();
    }

    public function checkPermission(string $permission, int $ref_id): bool
    {
        return $this->access->checkAccess($permission, '', $ref_id);
    }

    public function getParentRefId(int $ref_id): ?int
    {
        return $this->tree->getParentId($ref_id);
    }

    public function exists(int $ref_id): bool
    {
        return $this->tree->isInTree($ref_id);
    }

    public function findFirstAccessibleParentRefId(int $ref_id, string $permission = 'read'): ?int
    {
        if ($ref_id <= 0 || !$this->tree->isInTree($ref_id)) {
            return null;
        }

        $root_id = $this->tree->getRootId();
        $current = $ref_id;
        $visited = [];
        while (($parent = (int) $this->tree->getParentId($current)) > 0) {
            if (isset($visited[$parent])) {
                return null;
            }
            $visited[$parent] = true;
            if ($this->checkPermission($permission, $parent)) {
                return $parent;
            }
            if ($parent === $root_id) {
                return null;
            }
            $current = $parent;
        }

        return null;
    }

    public function getUserId(): int
    {
        return $this->user->getId();
    }

    public function isUserLoggedIn(): bool
    {
        return !$this->user->isAnonymous() && $this->user->getId() !== 0;
    }

    public function isPublicSectionActive(): bool
    {
        return (bool) $this->settings->get('pub_section');
    }

    public function builder(): URIBuilder
    {
        return $this->builder;
    }
}
