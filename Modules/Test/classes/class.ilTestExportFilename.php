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

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilTestExportFilename
{
    /**
     * @var ilObjTest
     */
    protected $test;

    /**
     * @var int
     */
    protected $timestamp = 0;

    /**
     * @param ilObjTest $test
     */
    public function __construct(ilObjTest $test)
    {
        $this->test = $test;
        $this->timestamp = time();
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param string $extension
     * @param string $additional
     * @return string
     * @throws ilException
     */
    public function getPathname($extension, $additional = ''): string
    {
        if (!is_string($extension) || !strlen($extension)) {
            throw new ilException('Missing file extension! Please pass a file extension of type string.');
        } elseif (substr_count($extension, '.') > 1 || (strpos($extension, '.') !== false && strpos($extension, '.') !== 0)) {
            throw new ilException('Please use at most one dot in your file extension.');
        } elseif (strpos($extension, '.') === 0) {
            $extension = substr($extension, 1);
        }

        if (!is_string($additional)) {
        } elseif (strlen($additional)) {
            if (strpos($additional, '__') === 0) {
                throw new ilException('The additional file part may not contain __ at the beginning!');
            }

            $additional = '__' . $additional . '_';
        } else {
            $additional = '_';
        }

        return $this->test->getExportDirectory() . DIRECTORY_SEPARATOR . $this->getTimestamp() . '__' . IL_INST_ID . '__' . $this->test->getType() . $additional . $this->test->getId() . '.' . $extension;
    }
}
