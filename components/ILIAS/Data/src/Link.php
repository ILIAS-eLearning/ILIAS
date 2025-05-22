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

namespace ILIAS\Data;

/**
 * A Link is the often used combination of a label and an URL.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Link
{
    protected string $label;
    protected URI $url;

    public function __construct(string $label, URI $url)
    {
        $this->label = $label;
        $this->url = $url;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getURL(): URI
    {
        return $this->url;
    }
}
