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

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Dropzone\File\Standard as StandardInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Button\Button;
use ilLanguage;

/**
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Standard extends File implements StandardInterface
{
    protected string $message = "";
    protected ?Button $upload_button = null;

    public function withMessage(string $message): self
    {
        $clone = clone $this;
        $clone->message = $message;
        return $clone;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function withUploadButton(Button $button): self
    {
        $clone = clone $this;
        $clone->upload_button = $button;
        return $clone;
    }

    public function getUploadButton(): ?Button
    {
        return $this->upload_button;
    }
}
