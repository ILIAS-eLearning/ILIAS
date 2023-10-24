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
     * @description Sharing links are used, for example, to share the current page in ILIAS. In ILIAS itself,
     * for example, the permanent link is used as a sharing link. Accepted are `\ILIAS\UI\Component\Link\Standard`
     * and `\ILIAS\UI\Component\Button\Shy`. Please note that modals which should be triggered e.g. via buttons must
     * be passed via the @see withModalsToRender method. The bundling of button and modal is up to the consumer.
     */
    public function withAdditionalSharingLink(Link\Standard|Button\Shy $link): static;

    /**
     * @description Utility link groups are thematically bundled collections of links. Title $title is expected to be
     * translated. The links can be supplied as `\ILIAS\UI\Component\Link\Standard` or `\ILIAS\UI\Component\Button\Shy`.
     * The groups are rendered in the order they are added to the Footer.
     * Please note that modals which should be triggered e.g. via buttons must be passed via the @see withModalsToRender
     * method. The bundling of button and modal is up to the consumer.
     */
    public function withAdditionalUtilityLinkGroup(string $title, Link\Standard|Button\Shy ...$links): static;

    /**
     * @description Meta-infos are short, simple, often technical information about the current page or the entire
     * installation. Due to the lack of a defined component, strings are currently accepted here. However, the strings
     * may only consist of sanitized plain text. The footer will require a stricter declaration here in the future,
     * as soon as it is available.
     */
    public function withMetaInfo(string ...$meta_info): static;

    /**
     * @description So that modals triggered by `\ILIAS\UI\Component\Button\Shy` buttons in
     * @see withAdditionalSharingLink or @see withAdditionalUtilityLinkGroup are rendered on the page,
     * they can be passed here. The bundling of button and modal is up to the consumer.
     */
    public function withModalsToRender(Modal\Modal ...$modal): static;

}
