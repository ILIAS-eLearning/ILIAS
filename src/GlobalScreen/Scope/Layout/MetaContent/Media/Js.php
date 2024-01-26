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
namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class Js
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Js extends AbstractMediaWithPath
{
    /**
     * @var bool
     */
    private $add_version_number;
    /**
     * @var int
     */
    private $batch;

    /**
     * Js constructor.
     * @param string $content
     * @param bool   $add_version_number
     * @param int    $batch
     */
    public function __construct(string $content, string $version, bool $add_version_number = true, int $batch = 2)
    {
        parent::__construct($content, $version);
        $this->add_version_number = $add_version_number;
        $this->batch = $batch;
    }

    /**
     * @return bool
     */
    public function addVersionNumber() : bool
    {
        return $this->add_version_number;
    }

    /**
     * @return int
     */
    public function getBatch() : int
    {
        return $this->batch;
    }
}
