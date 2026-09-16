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

namespace ILIAS\UI\Implementation\Component\Transfer;

use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\HasHelpTopics;
use ILIAS\UI\Component as C;
use ILIAS\Data\URI;

class Link implements C\Transfer\Link
{
    use ComponentHelper;
    use JavaScriptBindable;
    use HasAdditionalTransferMechanisms;

    public function __construct(
        C\Transfer\TransferMechanism $transfer_mechanism,
        protected URI $url,
        protected string $label,
    ) {
        $this->setTransferMechanisms([$transfer_mechanism]);
    }

    public function getUrl(): URI
    {
        return $this->url;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
