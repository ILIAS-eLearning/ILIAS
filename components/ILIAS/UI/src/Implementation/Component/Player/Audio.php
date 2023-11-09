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

namespace ILIAS\UI\Implementation\Component\Player;

use ILIAS\UI\Component as C;

/**
 * @author Alexander Killing <killing@leifos.de>
 * @package ILIAS\UI\Implementation\Component\Player
 */
class Audio extends Player implements C\Player\Audio
{
    private string $transcript;

    public function __construct(string $source, string $transcript)
    {
        parent::__construct($source);
        $this->transcript = $transcript;
    }

    public function getTranscription(): string
    {
        return $this->transcript;
    }
}
