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

namespace ILIAS\UI\examples\Input\Field\ProofOfWork;

/**
 * ---
 * description: >
 *   Proposal example for the Proof of Work input. The factory method is not
 *   implemented yet, so this example explains the intended construction instead
 *   of rendering the input.
 *
 * expected output: >
 *   ILIAS shows an info message. The message states that Proof of Work is
 *   proposed and not implemented yet, and it names the factory method
 *   proofOfWork. No puzzle and no input field are shown.
 * ---
 */
function base()
{
    // Intended construction once the input is implemented:
    // $proof = $ui->input()->field()->proofOfWork(
    //     '/challenge',
    //     'Confirm you are human',
    //     'This check runs in the browser. You do not need to solve a puzzle.'
    // )->withRequired(true);
    // $form = $ui->input()->container()->form()->standard('#', [
    //     $ui->input()->field()->section([$proof], 'Create account'),
    // ]);

    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $info = $ui->messageBox()->info(
        'Proof of Work is proposed and not implemented yet. '
        . 'The factory method input()->field()->proofOfWork($challenge_url, $label, $byline) '
        . 'throws NotImplementedException. '
        . 'The field will show a text label and store a client-generated proof. '
        . 'It will not show a puzzle and it will not verify the proof.'
    );

    return $renderer->render($info);
}
