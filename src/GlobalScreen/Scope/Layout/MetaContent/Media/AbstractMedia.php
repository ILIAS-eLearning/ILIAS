<?php

declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class Js
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractMedia
{
    protected string $content = "";

    protected string  $version = '';

    /**
     * AbstractMedia constructor.
     * @param string $content
     */
    public function __construct(string $content, string $version)
    {
        $this->content = $content;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
