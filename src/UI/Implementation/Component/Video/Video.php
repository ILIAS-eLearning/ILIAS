<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\UI\Implementation\Component\Video;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Class Video
 */
class Video implements C\Video\Video
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    private string $src = "";
    private array $subtitle_files = [];

    public function __construct(string $source)
    {
        $this->checkStringArg("src", $source);

        $this->src = $source;
    }

    public function withSource(string $source) : C\Video\Video
    {
        $this->checkStringArg("src", $source);

        $clone = clone $this;
        $clone->src = $source;
        return $clone;
    }

    public function getSource() : string
    {
        return $this->src;
    }

    public function withAdditionalSubtitleFile(string $lang_key, string $subtitle_file) : C\Video\Video
    {
        $this->checkStringArg("subtitle_file", $subtitle_file);
        $this->checkStringArg("lang_key", $lang_key);

        $clone = clone $this;
        $clone->subtitle_files[$lang_key] = $subtitle_file;
        return $clone;
    }

    public function getSubtitleFiles() : array
    {
        return $this->subtitle_files;
    }
}
