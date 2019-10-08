<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\Component;

/**
 * This describes the Page.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Page extends Component
{

    /**
     * @return Component[]
     */
    public function getContent();
}
