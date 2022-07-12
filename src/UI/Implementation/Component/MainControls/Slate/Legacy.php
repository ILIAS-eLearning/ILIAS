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
 
namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Legacy\Legacy as ILegacy;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Component;

/**
 * Legacy Slate
 */
class Legacy extends Slate implements ISlate\Legacy
{
    /**
     * @var Component[]
     */
    protected array $contents = [];

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $name,
        Symbol $symbol,
        ILegacy $content
    ) {
        parent::__construct($signal_generator, $name, $symbol);
        $this->contents = [$content];
    }

    /**
     * @inheritdoc
     */
    public function getContents() : array
    {
        return $this->contents;
    }

    public function withMappedSubNodes(callable $f) : ISlate\Legacy
    {
        return $this;
    }
}
