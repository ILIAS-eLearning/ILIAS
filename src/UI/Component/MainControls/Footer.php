<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls;

use ILIAS\UI\Component\Component;

/**
 * This describes the Footer.
 */
interface Footer extends Component
{
    /**
     * @return \ILIAS\UI\Component\Link\Standard|\ILIAS\UI\Component\Button\Shy[]
     */
    public function getLinks() : array;

    public function getText() : string;

    /**
     * @return \ILIAS\UI\Component\Modal\Modal[]
     */
    public function getModals() : array;

    /**
     * @param \ILIAS\UI\Component\Modal\Modal[] $modals
     * @return Footer
     */
    public function withModals(array $modals) : Footer;

    /**
     * @return \ILIAS\Data\URI | null
     */
    public function getPermanentURL();

    public function withPermanentURL(\ILIAS\Data\URI $url) : Footer;
}
