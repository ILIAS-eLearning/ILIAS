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

namespace ILIAS\FileDelivery\FileDeliveryTypes;

/**
 * Trait HttpServiceAware
 *
 * Header-based delivery methods do have the problem, that files can't be deleted in the same
 * request since e.g. X-SendFile wont find the file to delivery afterwards (see
 * https://www.ilias.de/mantis/view.php?id=20723 )
 *
 * This trait can be used in those delivery methods to send the output (especially the headers)
 * already to the browser and then delete the file afterwards.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0
 * @since   5.3
 *
 * @Internal
 */
trait HeaderBasedDeliveryHelper
{
    /**
     * @param \Closure $closure which sets the output-headers, e.g.
     *                          $response = $response->withHeader(self::X_SENDFILE,
     *                          realpath($path_to_file));
     */
    protected function sendFileUnbufferedUsingHeaders(\Closure $closure): void
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ob_start();

        $closure();

        ob_flush();
        ob_end_flush();
        flush();
    }
}
