<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

/**
 * Interface FileInput
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Input\Field
 */
interface FileInput extends FormInput, File
{
    /**
     * Returns a file input like this, with additional (metadata) inputs.
     *
     * @param Input[] $inputs
     * @return File
     */
    public function withMetadataInputs(array $inputs) : File;

    /**
     * Returns additional (metadata) inputs of this input.
     *
     * @return Input[]|null
     */
    public function getMetadataInputs() : ?array;
}