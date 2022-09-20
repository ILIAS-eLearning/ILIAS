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
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Alex Killing <alex.killing@gmx.de>
 */
abstract class ilObject2 extends ilObject
{
    /**
     * Constructor
     *
     * @param int  $a_id        reference_id or object_id
     * @param bool $a_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        $this->initType();
        parent::__construct($a_id, $a_reference);
    }

    abstract protected function initType(): void;

    final public function read(): void
    {
        parent::read();
        $this->doRead();
    }

    protected function doRead(): void
    {
    }


    final public function create(bool $a_clone_mode = false): int
    {
        if ($this->beforeCreate()) {
            $id = parent::create();
            if ($id) {
                $this->doCreate($a_clone_mode);
                return $id;
            }
        }
        return 0;
    }

    protected function doCreate(bool $clone_mode = false): void
    {
    }

    /**
     * If overwritten this method should return true,
     * there is currently no "abort" handling for cases where "false" is returned.
     *
     * @return bool
     */
    protected function beforeCreate(): bool
    {
        return true;
    }

    final public function update(): bool
    {
        if ($this->beforeUpdate()) {
            if (!parent::update()) {
                return false;
            }
            $this->doUpdate();

            return true;
        }

        return false;
    }

    protected function doUpdate(): void
    {
    }

    protected function beforeUpdate(): bool
    {
        return true;
    }

    final public function delete(): bool
    {
        if ($this->beforeDelete()) {
            if (parent::delete()) {
                $this->doDelete();
                $this->id = 0;
                return true;
            }
        }
        return false;
    }

    protected function doDelete(): void
    {
    }

    protected function beforeDelete(): bool
    {
        return true;
    }

    final public function cloneMetaData(ilObject $target_obj): bool
    {
        return parent::cloneMetaData($target_obj);
    }

    final public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        if ($this->beforeCloneObject()) {
            $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
            if ($new_obj instanceof ilObject2) {
                $this->doCloneObject($new_obj, $target_id, $copy_id);
                return $new_obj;
            }
        }
        return null;
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
    }

    protected function beforeCloneObject(): bool
    {
        return true;
    }
}
