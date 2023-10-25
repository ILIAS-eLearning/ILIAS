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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Assignments;

use ILIAS\MetaData\Repository\Dictionary\TagInterface;

class ActionAssignment implements ActionAssignmentInterface
{
    protected Action $action;
    protected TagInterface $tag;
    protected string $value;

    public function __construct(
        Action $action,
        TagInterface $tag,
        string $value = ''
    ) {
        $this->action = $action;
        $this->tag = $tag;
        $this->value = $value;
    }

    public function action(): Action
    {
        return $this->action;
    }

    public function tag(): TagInterface
    {
        return $this->tag;
    }

    public function value(): string
    {
        return $this->value;
    }
}
