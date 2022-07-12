<?php declare(strict_types = 1);

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

namespace ILIAS\SurveyQuestionPool\Export;

/**
 * Manages items in repository clipboard
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ImportManager
{
    protected ImportSessionRepository $repo;

    public function __construct(ImportSessionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getMobs() : array
    {
        return $this->repo->getMobs();
    }

    public function addMob(string $label, string $uri) : void
    {
        $this->repo->addMob($label, $uri);
    }

    public function clearMobs() : void
    {
        $this->repo->clearMobs();
    }
}
