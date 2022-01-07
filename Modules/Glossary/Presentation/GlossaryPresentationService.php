<?php

namespace ILIAS\Glossary\Presentation;

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Glossary presentation service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class GlossaryPresentationService
{
    protected \ilObjGlossary $glo;
    protected \ilObjGlossaryGUI $glo_gui;
    protected bool $offline;

    public function __construct(
        \ilObjUser $user,
        array $query_params,
        bool $offline = false,
        \ilCtrl $ctrl = null
    ) {
        global $DIC;

        $this->request = new GlossaryPresentationRequest($query_params);
        $this->user = $user;
        $this->ref_id = $this->request->getRequestedRefId();
        $this->glo_gui = new \ilObjGlossaryGUI([], $this->ref_id, true, false);
        /** @var \ilObjGlossary $glossary */
        $glossary = $this->glo_gui->object;
        $this->glo = $glossary;
        $this->offline = $offline;
    }

    public function getGlossaryGUI() : \ilObjGlossaryGUI
    {
        return $this->glo_gui;
    }

    public function getGlossary() : \ilObjGlossary
    {
        return $this->glo;
    }

    public function getRequest() : GlossaryPresentationRequest
    {
        return $this->request;
    }
}
