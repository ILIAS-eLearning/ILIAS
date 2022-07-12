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
 
namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

abstract class Avatar implements C\Symbol\Avatar\Avatar
{
    use ComponentHelper;
    use JavaScriptBindable;

    private string $username;
    protected string $label = '';

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function withLabel(string $text) : C\Symbol\Avatar\Avatar
    {
        $clone = clone $this;
        $clone->label = $text;
        return $clone;
    }

    public function getLabel() : string
    {
        return $this->label;
    }
}
