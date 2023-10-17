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

namespace ILIAS\MetaData\Editor\Presenter;

use ILIAS\MetaData\Elements\Data\DataInterface as ElementsDataInterface;

interface DataInterface
{
    public function dataValue(ElementsDataInterface $data): string;

    public function vocabularyValue(string $value): string;

    public function language(string $language): string;

    public function datetime(string $datetime): string;

    public function duration(string $duration): string;

    /**
     * @return string[]
     */
    public function durationLabels(): \Generator;
}
