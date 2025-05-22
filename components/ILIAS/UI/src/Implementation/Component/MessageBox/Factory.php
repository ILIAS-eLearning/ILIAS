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

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Component as C;

class Factory implements C\MessageBox\Factory
{
    public function failure(string $message_text): MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::FAILURE, $message_text);
    }

    public function success(string $message_text): MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::SUCCESS, $message_text);
    }

    public function info(string $message_text): MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::INFO, $message_text);
    }

    public function confirmation(string $message_text): MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::CONFIRMATION, $message_text);
    }
}
