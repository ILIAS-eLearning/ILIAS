<?php

namespace ILIAS\Glossary\Presentation;

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary presentation service
 *
 * @author killing@leifos.de
 */
class GlossaryPresentationService
{
    /**
     * @var \ilObjGlossary
     */
    protected $glo;

    /**
     * @var \ilObjGlossaryGUI
     */
    protected $glo_gui;

    /**
     * @var bool
     */
    protected $offline;

    /**
     * Constructor
     */
    public function __construct(
        \ilObjUser $user,
        array $query_params,
        bool $offline = false,
        \ilCtrl $ctrl = null
    ) {
        global $DIC;

        $ctrl = (is_null($ctrl))
            ? $DIC->ctrl()
            : $ctrl;

        $this->request = new GlossaryPresentationRequest($query_params);
        $this->user = $user;
        $this->ref_id = $this->request->getRequestedRefId();
        $this->glo_gui = new \ilObjGlossaryGUI([], $this->ref_id, true, false);
        $this->glo = $this->glo_gui->object;
        $this->offline = $offline;
    }

    /**
     * @return \ilObjGlossaryGUI
     */
    public function getGlossaryGUI() : \ilObjGlossaryGUI
    {
        return $this->glo_gui;
    }

    /**
     * @return \ilObjGlossary
     */
    public function getGlossary() : \ilObjGlossary
    {
        return $this->glo;
    }

    /**
     * Get request
     *
     * @return GlossaryPresentationRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
}
