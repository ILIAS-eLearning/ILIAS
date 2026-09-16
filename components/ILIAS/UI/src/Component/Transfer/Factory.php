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

namespace ILIAS\UI\Component\Transfer;

use ILIAS\Data\URI;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Link Transfer component is used to transfer an URL to another context
     *     or medium.
     *   composition: >
     *     The Link Transfer component consists of:
     *     - Primary Transfer Mechanism
     *     - Visual representation of the URL
     *     - Label describing the URL
     *     - Optional additional Transfer Mechanisms
     *     Whereas the Transfer Mechanisms consist of:
     *     - Clipboard: consists of a Button and a Glyph for its trigger.
     *     - Web Share: consists of a Button and a Glyph for its trigger.
     *     - QR Code: consists of a Button and a Glyph for its triggger, and a Modal
     *       featuring an Image for its transfer.
     *   effect: >
     *     When interacted with, the Link Transfer component transfers the URL address
     *     to another medium or context, according to the used Transfer Mechanism.
     *     When the Clipboard Transfer Mechanism is operated, the URL address is copied
     *     into the computers clipboard.
     *     When the Web Share Transfer Mechanism is operated, the browser opens the Web
     *     Share API.
     *     When the QR Code Transfer Mechanism is operated, the Modal is opened which
     *     shows the QR-code as an Image.
     *     After a transfer is completed, either successfully or not, an appropriate
     *     feedback is immediately shown to the user.
     *   rivals:
     *     Link: >
     *       A Link should be used to navigate to an URL instead of transferring it.
     *
     * background: https://docu.ilias.de/go/wiki/wpage_8762_1357
     *
     * context:
     *   - The permanent-link of the Footer.
     *
     * rules:
     *   wording:
     *     1: The label SHOULD name the target view or resource.
     *   ordering:
     *     1: The information MUST be presented first.
     *     2: Transfer mechanisms SHOULD be ordered by relevance.
     * ---
     * @param \ILIAS\UI\Component\Transfer\TransferMechanism $primary_transfer_mechanism
     * @param \ILIAS\Data\URI $url
     * @param string $label
     * @return \ILIAS\UI\Component\Transfer\Link
     */
    public function link(TransferMechanism $primary_transfer_mechanism, URI $url, string $label): Link;
}
