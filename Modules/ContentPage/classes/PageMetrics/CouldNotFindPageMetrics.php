<?php declare(strict_types=1);

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

namespace ILIAS\ContentPage\PageMetrics;

use ilException;

/**
 * Class CouldNotFindPageMetrics
 * @package ILIAS\ContentPage\PageMetrics
 * @author Michael Jansen <mjansen@databay.de>
 */
final class CouldNotFindPageMetrics extends ilException
{
    public static function by(int $contentPageId, int $pageId, string $language) : self
    {
        return new self(sprintf(
            'Could not find content page page metrics for page with page id %s and language %s for parent with id %s',
            $contentPageId,
            $pageId,
            $language
        ));
    }
}
