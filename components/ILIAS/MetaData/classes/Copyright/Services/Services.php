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

namespace ILIAS\MetaData\Copyright\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\MetaData\Copyright\DatabaseRepository;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\Renderer;

class Services
{
    protected RepositoryInterface $repository;
    protected RendererInterface $renderer;

    protected GlobalContainer $dic;

    public function __construct(
        GlobalContainer $dic,
    ) {
        $this->dic = $dic;
    }

    public function repository(): RepositoryInterface
    {
        if (isset($this->repository)) {
            return $this->repository;
        }
        return $this->repository = new DatabaseRepository(
            $this->dic->database()
        );
    }

    public function renderer(): RendererInterface
    {
        if (isset($this->renderer)) {
            return $this->renderer;
        }
        return $this->renderer = new Renderer(
            $this->dic->ui()->factory(),
            $this->dic->resourceStorage()
        );
    }
}
