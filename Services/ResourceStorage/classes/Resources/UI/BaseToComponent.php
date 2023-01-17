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

namespace ILIAS\Services\ResourceStorage\Resources\UI;

use ILIAS\Data\DataSize;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\Services\ResourceStorage\Resources\UI\Actions\ActionGenerator;
use ILIAS\Services\ResourceStorage\Resources\UI\Actions\NullActionGenerator;
use ILIAS\UI\Component\Image\Image;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseToComponent implements ToComponent
{
    public const SIZE_FACTOR = 1000;
    protected const DATE_FORMAT = 'd.m.Y H:i';
    protected \ilLanguage $language;
    protected \ILIAS\UI\Factory $ui_factory;
    protected ActionGenerator $action_generator;

    public function __construct(
        ?ActionGenerator $action_generator = null
    ) {
        global $DIC;
        $this->action_generator = $action_generator ?? new NullActionGenerator();
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('irss');
        $this->ui_factory = $DIC->ui()->factory();
    }


    protected function formatSize(int $size): string
    {
        $unit = match (true) {
            $size > self::SIZE_FACTOR * self::SIZE_FACTOR * self::SIZE_FACTOR => DataSize::GB,
            $size > self::SIZE_FACTOR * self::SIZE_FACTOR => DataSize::MB,
            $size > self::SIZE_FACTOR => DataSize::KB,
            default => DataSize::Byte,
        };

        $size = (int)(round((float)$size / (float)$unit, 2) * (float)$unit);

        return (string)(new DataSize($size, $unit));
    }

    protected function formatDate(\DateTimeImmutable $date): string
    {
        return $date->format(self::DATE_FORMAT);
    }
}
