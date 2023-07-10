<?php

declare(strict_types=1);

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

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Administration GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilAdminGSToolProvider extends AbstractDynamicToolProvider
{
    public const SHOW_ADMIN_TREE = 'show_admin_tree';

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main()->administration();
    }

    public function getToolsForContextStack(CalledContexts $called_contexts): array
    {
        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_ADMIN_TREE, true)) {
            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id, true);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Tree")
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getTree());
                });
        }

        return $tools;
    }

    private function getTree(): string
    {
        return (new ilAdministrationExplorerGUI("ilAdministrationGUI", "showTree"))->getHTML();
    }
}
