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

namespace ILIAS\GlobalScreen\GUI\I18n\MultiLanguage;

use ILIAS\GlobalScreen\GUI\Pons;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
interface TranslationsRepository
{
    public function store(Translations $translations): Translations;

    public function get(TranslatableItem $item): Translations;

    public function blank(TranslatableItem $item, string $language_code, string $translation): Translation;

    public function reset(): void;

    /**
     * @internal This is only due to the lack of a better place to put this.
     */
    public function retrieveCurrent(Pons $pons): TranslatableItem;

}
