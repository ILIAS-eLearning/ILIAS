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

namespace ILIAS\UI\Component\Launcher;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\MessageBox;
use Psr\Http\Message\ServerRequestInterface;

interface Launcher extends Component
{
    public function withDescription(string $description): self;

    /**
     * If the Launcher is configured with Inputs, an Roundtrip Modal is shown
     * with these Inputs.
     * The Form's Result is passed intto $evaluation as well as the Launcher instance itself.
     * Finally, you can add a MessageBox to the Modal with $instruction.
     *
     * a typical $evaluation might look like this:
     *  ```php
     * function (Result $result, Launcher &$launcher) use ($ctrl, $ui_factory) {
     *   if ($result->isOK() && $result->value()[0]) {
     *       $ctrl->redirectToURL(
     *           (string)$launcher->getTarget()->getURL()->withParameter('launcher_redirect', 'terms accepted')
     *       );
     *   }
     *   $launcher = $launcher->withStatusMessageBox($ui_factory->messageBox()->failure('You must accept the conditions.'));
     * ```
     */
    public function withInputs(Group $fields, \Closure $evaluation, MessageBox\MessageBox $instruction = null): self;

    public function withStatusIcon(null | Icon | ProgressMeter $status_icon): self;
    public function withStatusMessageBox(?MessageBox\MessageBox $status_message): self;

    /**
     * Labels the button that launches the process; if the process is not
     * launchable for the user, set the second parameter to false.
     * Also indicate, why the process is not launchable or provide information
     * what is blocking via withStatusMessageBox.
     */
    public function withButtonLabel(string $label, bool $launchable = true): self;

    public function withRequest(ServerRequestInterface $request): self;

    public function withModalSubmitLabel(?string $label): self;

    public function withModalCancelLabel(?string $label): self;
}
