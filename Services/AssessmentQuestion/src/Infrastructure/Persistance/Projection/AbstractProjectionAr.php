<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ActiveRecord;
use ilException;

abstract class AbstractProjectionAr extends ActiveRecord
{
    //
    // Not supported CRUD-Options:
    //
    /**
     * @throws ilException
     */
    public function store() {
        throw new ilException("Store is not supported - It's only possible to add new records to this store!");
    }


    /**
     * @throws ilException
     */
    public function update() {
        throw new ilException("Update is not supported - It's only possible to add new records to this store!");
    }

    /**
     * @throws ilException
     */
    public function save() {
        throw new ilException("Save is not supported - It's only possible to add new records to this store!");
    }

}