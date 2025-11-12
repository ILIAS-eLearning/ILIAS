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

namespace ILIAS\Search\GUI;

use ILIAS\Data\URI;
use ILIAS\Search\Presentation\Result\Sortation;

interface Actions
{
    public function search(): URI;

    public function remoteSearch(): URI;

    public function showSavedResults(): URI;

    public function applyFilter(): URI;

    public function switchResultPage(Sortation $sortation): URI;

    public function sortResultPage(): URI;

    public function autoComplete(): URI;

    public function isValidCommand(string $cmd): bool;
}
