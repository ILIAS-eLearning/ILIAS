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

namespace ILIAS\UI\Component\Link;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\HasContentLanguage;
use ILIAS\Data\LanguageTag;
use ILIAS\UI\Component\HasHelpTopics;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Link base interface.
 */
interface Link extends Component, HasContentLanguage, HasHelpTopics, JavaScriptBindable
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

    public function withLanguageOfReferencedContent(LanguageTag $language): Link;

    public function getLanguageOfReferencedResource(): ?LanguageTag;
}
