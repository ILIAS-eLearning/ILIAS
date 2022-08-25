<?php

declare(strict_types=1);

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

class SeqObjectiveMap
{
    public ?string $mGlobalObjID = null;

    public bool $mReadStatus = true;

    public bool $mReadMeasure = true;

    public bool $mReadRawScore = true;

    public bool $mReadMinScore = true;

    public bool $mReadMaxScore = true;

    public bool $mReadCompletionStatus = true;

    public bool $mReadProgressMeasure = true;

    public bool $mWriteStatus = false;

    public bool $mWriteMeasure = false;

    public bool $mWriteRawScore = false;

    public bool $mWriteMinScore = false;

    public bool $mWriteMaxScore = false;

    public bool $mWriteCompletionStatus = false;

    public bool $mWriteProgressMeasure = false;

    public function __construct()
    {
    }
}
