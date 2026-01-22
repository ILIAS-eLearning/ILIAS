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

namespace ILIAS\Badge\Table;

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Table\Data;

/**
 * This class provides a central helper method to wrap the content of a KS/UI > Table > Data
 * into a specific HTML container/block element (div).
 * Since badge images are of different types (PNG as raster image, SVG as vector image),
 * and the UI > Table > Data > Column types are agnostic of the content type, we need
 * this wrapper to apply specific CSS styles to the rendered HTML images to prevent visual issues
 * (like image stretching, alignment issues, etc.).
 * We were not able to adress this with IRSS flavours and picture mashines (crop etc.) to ensure
 * equal dimensions of the images (in contrast to ILIAS <= 9.x), since this heavily
 * relies on `Imagick` (which is not always available) and also causes issues because
 * of the different image types (transparancy handling etc.).
 * If the UI > Table > Data supports some non-responsive/fixed width image column types,
 * this class MUST NOT be used anymore.
 * See: https://mantis.ilias.de/view.php?id=46327, https://mantis.ilias.de/view.php?id=46551
 * @deprecated
 */
final class TableContentWrapper
{
    public function __construct(
        private readonly Renderer $r,
        private readonly Factory $f,
    ) {
    }

    public function wrap(Data $c): \ILIAS\UI\Component\Legacy\Content
    {
        return $this->f->legacy()->content(
            '<div class="badge_table-wrapper">' . $this->r->render($c) . '</div>'
        );
    }
}
