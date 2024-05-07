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

namespace ILIAS\ResourceStorage\Flavour\Engine;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ImagickEngineWithOptionalFFMpeg extends ImagickEngine
{
    private FFMpegEngine $ffmpeg;

    protected array $supported;

    public function __construct()
    {
        parent::__construct();
        $this->ffmpeg = new FFMpegEngine();
    }

    public function supports(string $suffix): bool
    {
        if ($this->ffmpeg->isRunning() && $this->ffmpeg->supports($suffix)) {
            return true;
        }

        return in_array(strtolower($suffix), $this->supported, true);
    }
}
