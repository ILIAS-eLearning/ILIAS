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

require_once(__DIR__ . '/../vendor/composer/vendor/autoload.php');
require_once(__DIR__ . '/../artifacts/bootstrap_default.php');
use ILIAS\GlobalScreen\Client\ContentRenderer;

if (PHP_SAPI !== 'cli') {
    \ilContext::init(\ilContext::CONTEXT_WAC);
    entry_point('ILIAS Legacy Initialisation Adapter');
    (new ContentRenderer())->run();
}
