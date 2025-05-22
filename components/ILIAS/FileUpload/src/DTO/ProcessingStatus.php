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

namespace ILIAS\FileUpload\DTO;

/**
 * Class ProcessingStatus
 *
 * The class is used by the processors to give feedback to the
 * UploadService about the validity of the current processed file.
 * This class only purpose is to transport data.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
final class ProcessingStatus
{
    /**
     * Upload is ok
     * @var int
     */
    public const OK = 1;
    /**
     * Upload got rejected by a processor
     * @var int
     */
    public const REJECTED = 2;
    /**
     * Upload is pending
     * @var int
     */
    public const PENDING = 3;
    /**
     * Upload got denied by a processor, the upload will be removed immediately
     * @var int
     */
    public const DENIED = 4;

    private int $code;

    /**
     * ProcessingStatus constructor.
     *
     * @param int    $code   The code OK or REJECTED.
     * @param string $message The message which should be set to make the rejection more
     *                       understandable for other developers.
     *
     * @throws \InvalidArgumentException Thrown if the given code is not OK or REJECTED. The
     *                                   exception can also be thrown if the given arguments are not
     *                                   of the correct type.
     */
    public function __construct(int $code, private string $message)
    {
        if (!in_array($code, [self::OK, self::REJECTED, self::DENIED, self::PENDING], true)) {
            throw new \InvalidArgumentException(
                'Invalid upload status code received. The code must be OK or REJECTED.'
            );
        }

        $this->code = $code;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
