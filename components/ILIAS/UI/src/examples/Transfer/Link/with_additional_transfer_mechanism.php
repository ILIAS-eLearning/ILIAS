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
 */

declare(strict_types=1);

namespace ILIAS\UI\examples\Transfer\Link;

use ILIAS\UI\Component\Transfer\TransferMechanism;

/**
 * ---
 * description: >
 *   ...
 *
 * expected output: >
 *   ...
 * ---
 */
function with_additional_transfer_mechanism(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $data_factory = new \ILIAS\Data\Factory();

    $component = $factory->transfer()->link(
        TransferMechanism::CLIPBOARD,
        $data_factory->uri("http://ilias.ch"),
        "Link to ILIAS",
    );

    $component = $component->withAdditionalTransferMechanism(TransferMechanism::WEB_SHARE);
    $component = $component->withAdditionalTransferMechanism(TransferMechanism::QR_CODE);

    return $renderer->render($component);
}
