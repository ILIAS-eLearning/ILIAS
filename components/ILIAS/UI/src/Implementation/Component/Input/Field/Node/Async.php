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
 */
 
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field\Node;

use ILIAS\UI\Component as C;
use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Async extends Node implements C\Input\Field\Node\Async
{
    /** @inheritDoc */
    public function __construct(
        protected URI $render_url,
        array $full_node_path,
        string $name,
        ?C\Symbol\Icon\Icon $icon
    ) {
        parent::__construct($full_node_path, $name, $icon);
    }

    public function getRenderUrl(): URI
    {
        return $this->render_url;
    }
}
