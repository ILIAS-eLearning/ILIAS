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
     * @var ilLMPresentationService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct(ilLMPresentationService $service)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->service = $service;
    }

    /**
     * render
     *
     * @return string
     */
    public function render()
    {
        $fac = new ilLMTOCExplorerGUIFactory();

        $exp = $fac->getExplorer($this->service, "ilTOC");
        //if (!$exp->handleCommand())
        return $exp->getHTML();
    }

    /**
     * Render into ls toc
     * @param
     */
    public function renderLSToc(\LSTOCBuilder $toc)
    {
        $fac = new ilLMTOCExplorerGUIFactory();
        $exp = $fac->getExplorer($this->service, "ilTOC");
        $exp->renderLSToc($toc);
    }
}
