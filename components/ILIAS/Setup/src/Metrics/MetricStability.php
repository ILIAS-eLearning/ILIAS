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

enum MetricStability
{
    /**
     * The stability of a metric tells how often we expect changes in the metric.
     */
    // Config metrics only change when some administrator explicitely changes
    // a configuration.
    case CONFIG;
    // Stable metric only change occassionally when some change in the installation
    // happened, e.g. a config change or an update.
    case STABLE;
    // Volatile metrics may change at every time even unexpectedly.
    case VOLATILE;
    // This should only be used for collections with mixed content.
    case MIXED;
}
