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
 
namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl\ViewControl as BaseControl;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Signal;

/**
 * This describes a Field Selection View Control
 */
interface FieldSelection extends BaseControl
{
    public const DEFAULT_DROPDOWN_LABEL = 'selection';
    public const DEFAULT_BUTTON_LABEL = 'refresh';

    public function getDropdownLabel() : string;
    public function getButtonLabel() : string;
    public function getInput() : Input;

    /**
     * This is an internal signal, used to submit the current choice
     */
    public function getSubmissionTrigger() : Signal;
    public function withResetSignals() : FieldSelection;
}
