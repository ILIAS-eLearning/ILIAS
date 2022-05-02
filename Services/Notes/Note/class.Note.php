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

namespace ILIAS\Notes;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Note
{
    public const PRIVATE = 1;
    public const PUBLIC = 2;

    protected int $id = 0;
    protected ?string $update_date;
    protected ?string $creation_date;
    protected int $author = 0;
    protected int $type = 0;
    protected string $text = "";
    protected Context $context;

    public function __construct(
        int $id,
        Context $context,
        string $text,
        int $author,
        int $type = self::PRIVATE,
        ?string $creation_date = null,
        ?string $update_date = null
    ) {
        $this->id = $id;
        $this->context = $context;
        $this->text = $text;
        $this->author = $author;
        $this->type = $type;
        $this->update_date = $update_date;
        $this->creation_date = $creation_date;
    }

    public function withCreationDate(string $creation_date) : self
    {
        $note = clone $this;
        $note->creation_date = $creation_date;
        return $note;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function getAuthor() : int
    {
        return $this->author;
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function getCreationDate() : ?string
    {
        return $this->creation_date;
    }

    public function getUpdateDate() : ?string
    {
        return $this->update_date;
    }
}
