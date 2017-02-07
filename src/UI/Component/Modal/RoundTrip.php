<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Button;

/**
 * Interface RoundTrip
 *
 * @package ILIAS\UI\Component\Modal
 */
interface RoundTrip extends Modal
{

    /**
     * @param array Button\Button[] $buttons
     * @return RoundTrip
     */
    public function withButtons(array $buttons);


}
