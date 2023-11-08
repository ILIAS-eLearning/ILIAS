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

namespace ILIAS\MetaData\Editor\Full\Services\Actions;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Editor\Http\LinkFactoryInterface as LinkFactory;
use ILIAS\Data\URI;
use ILIAS\MetaData\Editor\Http\Command;
use ILIAS\MetaData\Editor\Http\Parameter;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Elements\ElementInterface;

class LinkProvider
{
    protected LinkFactory $link_factory;
    protected PathFactory $path_factory;

    public function __construct(
        LinkFactory $link_factory,
        PathFactory $path_factory
    ) {
        $this->link_factory = $link_factory;
        $this->path_factory = $path_factory;
    }

    public function create(
        PathInterface $base_path,
        ElementInterface $to_be_created
    ): URI {
        return $this->getLink(
            $base_path,
            $this->path_factory->toElement($to_be_created, true),
            Command::CREATE_FULL
        );
    }

    public function update(
        PathInterface $base_path,
        ElementInterface $to_be_updated
    ): URI {
        return $this->getLink(
            $base_path,
            $this->path_factory->toElement($to_be_updated, true),
            Command::UPDATE_FULL
        );
    }

    public function delete(
        PathInterface $base_path,
        ElementInterface $to_be_deleted
    ): URI {
        return $this->getLink(
            $base_path,
            $this->path_factory->toElement($to_be_deleted, true),
            Command::DELETE_FULL
        );
    }

    protected function getLink(
        PathInterface $base_path,
        PathInterface $action_path,
        Command $action_cmd
    ): URI {
        return $this->link_factory
            ->custom($action_cmd)
            ->withParameter(Parameter::BASE_PATH, $base_path->toString())
            ->withParameter(Parameter::ACTION_PATH, $action_path->toString())
            ->get();
    }
}
