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

namespace ILIAS\Setup\Metrics;

enum MetricType
{
    /**
     * The type of the metric tells what to expect of the values.
     */
    // Simply a yes or a no.
    case BOOL;
    // A number that always increases.
    case COUNTER;
    // A numeric value to measure some quantity of the installation.
    case GAUGE;
    // A timestamp to inform about a certain event in the installation.
    case TIMESTAMP;
    // Some textual information about the installation. Prefer using one of the
    // other types.
    case TEXT;
    // A collection of metrics that contains multiple named metrics.
    case COLLECTION;
}
