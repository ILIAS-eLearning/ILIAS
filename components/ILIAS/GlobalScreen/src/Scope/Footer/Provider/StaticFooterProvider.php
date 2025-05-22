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

namespace ILIAS\GlobalScreen\Scope\Footer\Provider;

use ILIAS\GlobalScreen\Provider\StaticProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Permanent;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface StaticFooterProvider extends StaticProvider, FooterProviderInterface
{
    /**
     * @return TopParentItem[] These are Slates which will be
     * available for configuration.
     */
    public function getGroups(): array;

    /**
     * @return isItem[] These are Entries which will be available for
     * configuration.
     */
    public function getEntries(): array;

    public function getAdditionalTexts(): array;

    public function getPermanentURI(): ?Permanent;
}
