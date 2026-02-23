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

namespace ILIAS\GlobalScreen\GUI\I18n;

use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationsRepository;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class MultiLanguage
{
    public function __construct(
        private ?TranslationsRepository $repository = null
    ) {
    }

    public function isSupported(): bool
    {
        return $this->repository !== null;
    }

    public function repository(): ?TranslationsRepository
    {
        return $this->repository;
    }

}
