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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval\Info;

use ilDateTime;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\FactoryInterface as IteratorFactoryInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\LPInterface as LPInfoInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\LPInterface as LPIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ObjectDataInterface as ObjectDataInfoInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\ObjectDataInterface as ObjectDataIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\CombinedInterface as CombinedInfoInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\CombinedInterface as CombinedIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ViewInterface as ViewInfoInterface;

interface FactoryInterface
{
    public function iterator(): IteratorFactoryInterface;

    public function lp(
        int $user_id,
        int $object_id,
        int $lp_status,
        int $percentage,
        int $lp_mode,
        int $spent_seconds,
        ilDateTime $status_changed,
        int $visits,
        int $read_count,
        bool $has_percentage
    ): LPInfoInterface;

    public function combined(
        LPinfoInterface $lp_info,
        ObjectDataInfoInterface $object_data_info
    ): CombinedInfoInterface;

    public function objectData(
        int $object_id,
        string $title,
        string $description,
        string $type
    ): ObjectDataInfoInterface;

    public function view(
        ObjectDataIteratorInterface $object_data_iterator,
        LPIteratorInterface $lp_iterator,
        CombinedIteratorInterface $combined_iterator
    ): ViewInfoInterface;
}
