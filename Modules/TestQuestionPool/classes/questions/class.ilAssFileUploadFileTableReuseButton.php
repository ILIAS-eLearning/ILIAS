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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/TestQuestionPool
 */
class ilAssFileUploadFileTableReuseButton extends ilAssFileUploadFileTableCommandButton
{
    public const ACTION = 'reuse';

    public function __construct($type)
    {
        parent::__construct($type);
        $this->setAction(self::ACTION);
        $this->setCaption($this->lng()->txt('ass_file_upload_reuse_btn'), false);
    }

    public static function getInstance(): self
    {
        return new self(self::TYPE_SUBMIT);
    }
}
