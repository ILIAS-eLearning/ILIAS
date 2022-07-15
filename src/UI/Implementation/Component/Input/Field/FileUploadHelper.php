<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\FileUpload;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait FileUploadHelper
{
    protected UploadHandler $upload_handler;
    protected array $accepted_mime_types = [];
    protected bool $has_metadata_inputs = false;
    protected int $max_file_amount = 1;

    /**
     * @var int MUST be set manually because the file-size has to be gathered programatically.
     */
    protected int $max_file_size;

    public function getUploadHandler() : UploadHandler
    {
        return $this->upload_handler;
    }

    public function withMaxFileSize(int $size_in_bytes) : FileUpload
    {
        if ($size_in_bytes > $this->getMaxFileSizeDefault()) {
            throw new \InvalidArgumentException("Given file-size exceeds the limit of {$this->getMaxFileSizeDefault()} bytes.");
        }

        $clone = clone $this;
        $clone->max_file_size = $size_in_bytes;

        return $clone;
    }

    public function getMaxFileSize() : int
    {
        return $this->max_file_size;
    }

    public function withMaxFiles(int $max_file_amount) : FileUpload
    {
        $clone = clone $this;
        $clone->max_file_amount = $max_file_amount;

        return $clone;
    }

    public function getMaxFiles() : int
    {
        return $this->max_file_amount;
    }

    public function withAcceptedMimeTypes(array $mime_types) : FileUpload
    {
        $clone = clone $this;
        $clone->accepted_mime_types = $mime_types;

        return $clone;
    }

    public function getAcceptedMimeTypes() : array
    {
        return $this->accepted_mime_types;
    }

    protected function getMaxFileSizeDefault() : int
    {
        return (int) \ilFileUtils::getUploadSizeLimitBytes();
    }
}
