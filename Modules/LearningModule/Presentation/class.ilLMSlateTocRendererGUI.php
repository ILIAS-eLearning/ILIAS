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

/**
 * Renders TOC for slate
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMSlateTocRendererGUI
{
    protected ilObjUser $user;
    protected ilLMPresentationService $service;

    public function __construct(
        ilLMPresentationService $service
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->service = $service;
    }

    public function render() : string
    {
        $fac = new ilLMTOCExplorerGUIFactory();

        $exp = $fac->getExplorer($this->service, "ilTOC");
        //if (!$exp->handleCommand())
        return $exp->getHTML();
    }

    /**
     * Render into ls toc
     */
    public function renderLSToc(\LSTOCBuilder $toc) : void
    {
        $fac = new ilLMTOCExplorerGUIFactory();
        $exp = $fac->getExplorer($this->service, "ilTOC");
        $exp->renderLSToc($toc);
    }
}
