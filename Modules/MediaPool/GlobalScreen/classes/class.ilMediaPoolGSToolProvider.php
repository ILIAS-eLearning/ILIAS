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

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * @author Alex Killing <killing@leifos.com>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMediaPoolGSToolProvider extends AbstractDynamicToolProvider
{
    public const SHOW_FOLDERS_TOOL = 'show_folders_tool';

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->repository();
    }

    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        global $DIC;
        
        $access = $DIC->access();

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_FOLDERS_TOOL, true)) {
            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $ref_id = $called_contexts->current()->getReferenceId()->toInt();

            if (!$access->checkAccess("read", "", $ref_id)) {
                return $tools;
            }

            $title = "Folders";
            $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("icon_fldm.svg"), $title);
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(function () use ($l, $ref_id) {
                    return $l($this->getTree($ref_id));
                });
        }

        return $tools;
    }


    private function getTree(int $ref_id) : string
    {
        try {
            /** @var ilObjMediaPool $pool */
            $pool = ilObjectFactory::getInstanceByRefId($ref_id);
            $pool_gui = new ilObjMediaPoolGUI($ref_id);
            $exp = new ilMediaPoolExplorerGUI($pool_gui, "listMedia", $pool);

            return $exp->getHTML(true);
        } catch (Exception $e) {
            return "";
        }
    }
}
