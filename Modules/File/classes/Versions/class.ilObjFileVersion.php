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
 * Class ilObjFileVersion
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileVersion extends ArrayObject
{
    /**
     * @inheritDoc
     */
    public function __construct(array $input = [])
    {
        parent::__construct($input);
        foreach ($input as $k => $v) {
            $this->{$k} = $v;
        }
    }

    /**
     * @inheritDoc
     */
    public function getArrayCopy(): array
    {
        $a = [];
        $r = new ReflectionClass($this);
        foreach ($r->getProperties() as $p) {
            $p->setAccessible(true);
            $a[$p->getName()] = $p->getValue($this);
        }
        return $a;
    }

    protected string $date = '';
    protected int $user_id = 0;
    protected int $obj_id = 0;
    protected string $obj_type = '';
    protected string $action = '';
    protected string $info_params = '';
    protected string $user_comment = '';
    protected int $hist_entry_id = 1;
    protected ?string $title = '';
    protected string $filename = '';
    protected string $version = '';
    protected string $max_version = '';
    protected string $rollback_version = '';
    protected string $rollback_user_id = '';
    protected int $size = 0;

    public function offsetGet($index)
    {
        return $this->{$index};
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): self
    {
        $this->obj_id = $obj_id;
        return $this;
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function setObjType(string $obj_type): self
    {
        $this->obj_type = $obj_type;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getInfoParams(): string
    {
        return $this->info_params;
    }

    public function setInfoParams(string $info_params): self
    {
        $this->info_params = $info_params;
        return $this;
    }

    public function getUserComment(): string
    {
        return $this->user_comment;
    }

    public function setUserComment(string $user_comment): self
    {
        $this->user_comment = $user_comment;
        return $this;
    }

    public function getHistEntryId(): int
    {
        return $this->hist_entry_id;
    }

    public function setHistEntryId(int $hist_entry_id): self
    {
        $this->hist_entry_id = $hist_entry_id;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }
}
