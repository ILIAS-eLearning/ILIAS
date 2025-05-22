<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Link\Standard as Link;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Modal;

interface Footer extends Component
{
    /**
     * Get a Footer like this but add an additional, named group of links or shy-buttons to
     * the link-group section.
     *
     * @param array<Link|Shy> $actions only use Shy buttons if they trigger signal(s).
     */
    public function withAdditionalLinkGroup(string $title, array $actions): self;

    /**
     * Get a Footer like this but add an additional link or shy-button to the links section.
     *
     * @param Link|Shy ...$actions only use Shy buttons if they trigger signal(s).
     */
    public function withAdditionalLink(Link|Shy ...$actions): self;

    /**
     * Get a Footer like this but add an additional Icon to the icons section. The Icon may
     * also trigger an action or signal to trigger a dialog.
     */
    public function withAdditionalIcon(Icon $icon, URI|Signal|null $action = null): self;

    /**
     * Get a Footer like this but add additional text information to the meta section.
     */
    public function withAdditionalText(string ...$texts): self;

    /**
     * Get a Footer like this but with a permanent URL to the current page, which can
     * be copied by the users.
     */
    public function withPermanentURL(URI $url): self;

    /**
     * @deprecated injecting modals into the footer will be removed in the future.
     * Get a Footer like this but add an additional modal wich will be rendered to the page. Please be aware that
     * you must add the Triggerer for the modal to the footer as well and connect the modal to the triggerer.
     * Do not use this to add Modals not related to the Footer.
     */
    public function withAdditionalModal(Modal\RoundTrip $modal): self;
}
