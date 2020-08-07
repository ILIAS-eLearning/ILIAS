<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\Modal;

/**
 * This describes the Footer.
 */
interface Footer extends Component
{
    /**
     * @return Link\Standard[]
     */
    public function getLinks() : array;

    public function getText() : string;

    /**
     * @return array<Modal\RoundTrip, Button\Shy>[]
     */
    public function getModals() : array;

    /**
     * @param Modal\RoundTrip $roundTripModal
     * @param Button\Shy $shyButton
     * @return Footer
     */
    public function withAdditionalModalAndTrigger(
        Modal\RoundTrip $roundTripModal,
        Button\Shy $shyButton
    ) : Footer;

    /**
     * @return URI|null
     */
    public function getPermanentURL();

    public function withPermanentURL(URI $url) : Footer;
}
