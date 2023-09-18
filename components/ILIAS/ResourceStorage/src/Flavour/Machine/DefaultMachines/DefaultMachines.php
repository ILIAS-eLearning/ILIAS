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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DefaultMachines
{
    private array $machines = [];

    public function __construct()
    {
        $this->machines = [
            MakeGreyScale::ID => MakeGreyScale::class,
            CropSquare::ID => CropSquare::class,
            FitSquare::ID => FitSquare::class,
            ExtractPages::ID => ExtractPages::class,
        ];
    }


    public function get(): array
    {
        return $this->machines;
    }
}
