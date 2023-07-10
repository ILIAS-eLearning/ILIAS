<?php declare(strict_types=1);

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

namespace ILIAS\UI\Component;

use ILIAS\Data\LanguageTag;

interface HasContentLanguage
{
    /**
     * If the link text also is not translated (e.g., because it is a formal title
     * that should be kept in the original language), you should add the language attributes to the anchor element.
     */
    public function withContentLanguage(LanguageTag $language): self;

    /**
     * See comment in withContentLanguage
     */
    public function getContentLanguage(): ?LanguageTag;
}
