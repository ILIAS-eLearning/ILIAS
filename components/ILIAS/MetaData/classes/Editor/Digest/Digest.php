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

namespace ILIAS\MetaData\Editor\Digest;

use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;

class Digest
{
    protected ContentAssembler $content_assembler;
    protected ManipulatorAdapter $manipulator_adapter;

    public function __construct(
        ContentAssembler $content_assembler,
        ManipulatorAdapter $manipulator_adapter
    ) {
        $this->content_assembler = $content_assembler;
        $this->manipulator_adapter = $manipulator_adapter;
    }

    /**
     * @return StandardForm[]|InterruptiveModal[]|string[]
     */
    public function getContent(
        SetInterface $set,
        ?RequestForFormInterface $request = null
    ): \Generator {
        yield from $this->content_assembler->get($set, $request);
    }

    public function updateMD(
        SetInterface $set,
        RequestForFormInterface $request
    ): bool {
        return $this->manipulator_adapter->update($set, $request);
    }
}
