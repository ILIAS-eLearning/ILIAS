<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components;

use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
interface PageComponentEditor
{
    /**
     * Get rendered editor elements
     * @param UIWrapper $this
     * @param string       $page_type
     * @param \ilPageObjectGUI $page_gui
     * @param int          $style_id
     * @return array
     */
    public function getEditorElements(UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id) : array;

    /**
     * Get rendered editor elements
     * @param UIWrapper $this
     * @param string       $page_type
     * @param \ilPageObjectGUI $page_gui
     * @param int          $style_id
     * @param string        $pcid
     * @return string
     */
    public function getEditComponentForm(UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id, $pcid) : string;
}
