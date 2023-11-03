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

namespace ILIAS\MetaData\Services\DataHelper;

use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\DataHelper\DataHelperInterface as InternalDataHelper;
use ILIAS\MetaData\Presentation\DataInterface as DataPresentation;

class DataHelper implements DataHelperInterface
{
    protected InternalDataHelper $internal_helper;
    protected DataPresentation $data_presentation;

    public function __construct(
        InternalDataHelper $internal_helper,
        DataPresentation $data_presentation
    ) {
        $this->data_presentation = $data_presentation;
        $this->internal_helper = $internal_helper;
    }

    public function makePresentable(DataInterface $data): string
    {
        return $this->data_presentation->dataValue($data);
    }

    /**
     * @return int[]|null[]
     */
    public function durationToArray(string $duration): array
    {
        $array = [];
        foreach ($this->internal_helper->durationToIterator($duration) as $value) {
            $array[] = is_null($value) ? $value : (int) $value;
        }
        return $array;
    }

    public function durationToSeconds(string $duration): int
    {
        return $this->internal_helper->durationToSeconds($duration);
    }

    public function datetimeToObject(string $datetime): \DateTimeImmutable
    {
        return $this->internal_helper->datetimeToObject($datetime);
    }

    public function durationFromIntegers(
        ?int $years,
        ?int $months,
        ?int $days,
        ?int $hours,
        ?int $minutes,
        ?int $seconds
    ): string {
        return $this->internal_helper->durationFromIntegers(
            $years,
            $months,
            $days,
            $hours,
            $minutes,
            $seconds
        );
    }

    public function datetimeFromObject(\DateTimeImmutable $object): string
    {
        return $this->internal_helper->datetimeFromObject($object);
    }
}
