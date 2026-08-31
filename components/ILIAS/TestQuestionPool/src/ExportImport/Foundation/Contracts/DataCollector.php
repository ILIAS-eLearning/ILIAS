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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

/**
 * Interface for data collectors that extract and assemble data relevant for
 * question exports into exportable structures.
 *
 * Implementations use the provided repositories and domain objects as return
 * values and follow a builder-style API: configuration methods are fluent and
 * return the collector instance itself, while query methods typically expose
 * generators to stream potentially large data sets efficiently for export.
 */
interface DataCollector
{
    //
}
