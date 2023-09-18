<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getLinks(): array;

    public function getText(): string;

    /**
     * @return array<Modal\RoundTrip, Button\Shy>[]
     */
    public function getModals(): array;

    public function withAdditionalModalAndTrigger(Modal\RoundTrip $roundTripModal, Button\Shy $shyButton): Footer;

    public function getPermanentURL(): ?URI;

    public function withPermanentURL(URI $url): Footer;
}
