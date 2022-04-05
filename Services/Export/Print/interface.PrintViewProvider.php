<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Export;

use ilPropertyFormGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
interface PrintViewProvider
{
    /**
     * form which is featured in the modal
     * form target is modified to open in new window (not yet possible with ks forms)
     * the print/pdf message is added automatically
     * @return ilPropertyFormGUI|null
     */
    public function getSelectionForm() : ?ilPropertyFormGUI;

    public function getOnSubmitCode() : string;

    public function autoPageBreak() : bool;

    /**
     * @return string[]
     */
    public function getPages() : array;

    /**
     * @return callable[]   each callable gets the $tpl passed to inject css/js/...
     */
    public function getTemplateInjectors() : array;
}
