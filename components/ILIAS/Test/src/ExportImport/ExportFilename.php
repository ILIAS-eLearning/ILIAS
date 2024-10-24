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

namespace ILIAS\Test\ExportImport;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup components\ILIASTest
 */
class ExportFilename
{
    private ?string $path = null;

    public function __construct(
        private int $test_id
    ) {
    }

    public function getPathname(string $extension = '', string $additional = ''): string
    {
        if ($this->path !== null) {
            return $this->path;
        }
        if ($extension === '') {
            throw new \ilException('Missing file extension! Please pass a file extension of type string.');
        }

        if (substr_count($extension, '.') > 1 || (strpos($extension, '.') !== false && strpos($extension, '.') !== 0)) {
            throw new \ilException('Please use at most one dot in your file extension.');
        }

        if (strpos($extension, '.') === 0) {
            $extension = substr($extension, 1);
        }

        $corrected_additional = '_';
        if ($additional !== '') {
            if (strpos($additional, '__') === 0) {
                throw new ilException('The additional file part may not contain __ at the beginning!');
            }

            $corrected_additional = '__' . $additional . '_';
        }

        $this->path = \ilFileUtils::ilTempnam() . '__' . IL_INST_ID
            . $corrected_additional . $this->test_id . '.' . $extension;
        return $this->path;
    }
}
