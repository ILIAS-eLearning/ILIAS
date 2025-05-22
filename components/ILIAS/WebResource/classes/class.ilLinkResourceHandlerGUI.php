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

use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Handles user interface for link resources.
 * @author       Alex Killing <alex.killing@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilLinkResourceHandlerGUI: ilObjLinkResourceGUI
 * @ingroup components\ILIASWebResource
 */
class ilLinkResourceHandlerGUI implements ilCtrlBaseClassInterface
{
    protected Refinery $refinery;
    protected HTTPService $http;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilNavigationHistory $navigationHistory;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->navigationHistory = $DIC['ilNavigationHistory'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand(): void
    {
        global $DIC;

        $ref_id = $this->http->wrapper()->query()->has('ref_id') ?
            $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            ) : 0;

        $next_class = $this->ctrl->getNextClass($this);
        if ($this->access->checkAccess("read", "", $ref_id)) {
            $this->navigationHistory->addItem(
                $ref_id,
                "ilias.php?baseClass=ilLinkResourceHandlerGUI&cmd=infoScreen&ref_id=" . $ref_id,
                "webr"
            );
        }
        if ($next_class == "") {
            $this->ctrl->saveParameter($this, 'ref_id');
            $this->ctrl->redirectByClass(
                ilObjLinkResourceGUI::class,
                $this->ctrl->getCmd()
            );
        }
        switch ($next_class) {
            case strtolower(ilObjLinkResourceGUI::class):
                $link_gui = new ilObjLinkResourceGUI(
                    $ref_id,
                    ilObject2GUI::REPOSITORY_NODE_ID
                );
                $this->ctrl->forwardCommand($link_gui);
                break;
        }
        $this->tpl->printToStdout();
    }
}
