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

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\MainControls as IMainControls;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements IMainControls\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected Slate\Factory $slate_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        Slate\Factory $slate_factory
    ) {
        $this->signal_generator = $signal_generator;
        $this->slate_factory = $slate_factory;
    }

    public function metaBar(): MetaBar
    {
        return new MetaBar($this->signal_generator);
    }

    public function mainBar(): MainBar
    {
        return new MainBar($this->signal_generator);
    }

    public function slate(): Slate\Factory
    {
        return $this->slate_factory;
    }

    public function footer(): Footer
    {
        return new Footer();
    }

    public function modeInfo(string $title, URI $close_action): ModeInfo
    {
        return new ModeInfo($title, $close_action);
    }

    public function systemInfo(string $headline, string $information_text): SystemInfo
    {
        return new SystemInfo(
            $this->signal_generator,
            $headline,
            $information_text
        );
    }
}
