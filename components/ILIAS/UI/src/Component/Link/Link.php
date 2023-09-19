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

namespace ILIAS\UI\Component\Link;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\HasContentLanguage;
use ILIAS\Data\LanguageTag;
use ILIAS\UI\Component\HasHelpTopics;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Modal\DialogContent;

/**
 * Link base interface.
 */
interface Link extends Component, HasContentLanguage, HasHelpTopics, JavaScriptBindable, DialogContent
{
    /**
     * Get the action url of a link
     */
    public function getAction(): string;

    /**
     * Set if link should be opened in new viewport
     */
    public function withOpenInNewViewport(bool $open_in_new_viewport): Link;

    public function getOpenInNewViewport(): ?bool;

    /**
     * The hreflang attribute indicates the language of content targeted by links.
     * It is helpful though not required to add this information to links for which the target will
     * not be translated in this process. If the link text also is not translated (e.g., because it is a formal title
     * that should be kept in the original language), you should also add the language attributes to the anchor element.
     */
    public function withLanguageOfReferencedContent(LanguageTag $language): Link;

    /**
     * See comment in withLanguageOfReferencedContent
     */
    public function getLanguageOfReferencedResource(): ?LanguageTag;

    /**
     * Relationships between the current and the referenced page are
     * added as a rel attribute.
     */
    public function withAdditionalRelationshipToReferencedResource(Relationship $type): Link;

    /**
     * @return IsRelationship[]
     */
    public function getRelationshipsToReferencedResource(): array;
}
