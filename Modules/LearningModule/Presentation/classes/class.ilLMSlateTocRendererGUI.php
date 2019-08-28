<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Renders TOC for slate
 *
 * @author killing@leifos.de
 */
class ilLMSlateTocRendererGUI
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
    }

    /**
     * render
     *
     * @return string
     */
    public function render()
    {
        $fac = new ilLMTOCExplorerGUIFactory();
        $service = new ilLMPresentationService($this->user, $_GET);
        $exp = $fac->getExplorer($service, "ilTOC");
        //if (!$exp->handleCommand())
        return $exp->getHTML();
    }

}