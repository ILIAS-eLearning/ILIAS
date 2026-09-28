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

namespace ILIAS\Mail\Mime\Presentation;

use Closure;
use ilSetting;

/**
 * Composes the body in whichever format the installation is configured to send.
 */
final readonly class ConfiguredMailBodyComposer implements MailBodyComposer
{
    /**
     * @param Closure(): MailBodyComposer $html_composer Composing HTML requires the UI service and a
     *                                                   look at the installation title, so the composer
     *                                                   is only ever built for HTML delivery.
     */
    public function __construct(
        private ilSetting $settings,
        private Closure $html_composer,
        private MailBodyComposer $plain_text_composer
    ) {
    }

    public function compose(MailBodySource $source): ComposedMailBody
    {
        if ($this->settings->get('mail_send_html', '0')) {
            return ($this->html_composer)()->compose($source);
        }

        return $this->plain_text_composer->compose($source);
    }
}
