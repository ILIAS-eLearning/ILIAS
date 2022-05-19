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
 *********************************************************************/
 
namespace ILIAS\ResourceStorage\Consumer;

/**
 * Class AbsolutePathConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class AbsolutePathConsumer extends BaseConsumer
{
    protected string $absolute_path = '';

    public function getAbsolutePath() : string
    {
        $this->run();
        return $this->absolute_path;
    }

    public function run() : void
    {
        $revision = $this->getRevision();

        $stream = $this->storage_handler->getStream($revision);

        $this->absolute_path = (string) $stream->getMetadata('uri');
    }
}
