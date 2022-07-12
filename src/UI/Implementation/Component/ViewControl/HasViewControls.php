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
 
namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl\HasViewControls as HasViewControlsInterface;

/**
 * Trait for panels supporting view controls
 */
trait HasViewControls
{
    protected ?array $view_controls = null;

    /**
     * @inheritDoc
     */
    public function withViewControls(array $view_controls) : HasViewControlsInterface
    {
        /**
         * @var $clone HasViewControlsInterface
         */
        $clone = clone $this;
        $clone->view_controls = $view_controls;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function getViewControls() : ?array
    {
        return $this->view_controls;
    }
}
