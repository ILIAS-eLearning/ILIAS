<?php

declare(strict_types=1);

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

namespace ILIAS\Mail\Service;

use ILIAS\DI\Container;
use ilMailMimeTransportFactory;
use ilMailMimeSenderFactory;

class MimeMailService
{
    /** @var Container $dic */
    protected $dic;

    public function __construct(Container $DIC)
    {
        $this->dic = $DIC;

        if (!isset($this->dic['mail.mime.transport.factory'])) {
            $this->dic['mail.mime.transport.factory'] = static function (Container $c) : ilMailMimeTransportFactory {
                return new ilMailMimeTransportFactory($c->settings(), $c->event());
            };
        }

        if (!isset($this->dic['mail.mime.sender.factory'])) {
            $this->dic['mail.mime.sender.factory'] = static function (Container $c) : ilMailMimeSenderFactory {
                return new ilMailMimeSenderFactory($c->settings());
            };
        }
    }

    public function transportFactory() : ilMailMimeTransportFactory
    {
        return $this->dic['mail.mime.transport.factory'];
    }

    public function senderFactory() : ilMailMimeSenderFactory
    {
        return $this->dic['mail.mime.sender.factory'];
    }
}
