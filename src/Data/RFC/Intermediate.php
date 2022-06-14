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

namespace ILIAS\Data\RFC;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;

class Intermediate
{
    private string $todo;
    private string $accepted;

    public function __construct(string $todo, string $accepted = '')
    {
        $this->todo = $todo;
        $this->accepted = $accepted;
    }

    public function value() : int
    {
        return ord($this->todo);
    }

    public function accept() : Result
    {
        return new Ok(new self(
            substr($this->todo, 1),
            $this->accepted . substr($this->todo, 0, 1)
        ));
    }

    public function reject() : Result
    {
        return new Error('Rejected.');
    }

    public function accepted() : string
    {
        return $this->accepted;
    }

    public function done() : bool
    {
        return '' === $this->todo;
    }
}
