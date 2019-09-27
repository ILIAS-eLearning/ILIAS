<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class KprimChoiceEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FileUploadEditorConfiguration extends AbstractConfiguration {
    
    /**
     * @var ?int
     */
    protected $maximum_size;
 
    /**
     * @var ?string
     */
    protected $allowed_extensions;
    
    const AMOUNT_ONE = 1;
    const AMOUNT_MANY = 2;
    /**
     * @var int
     */
    protected $upload_type;
    
    /**
     * @param int $maximum_size
     * @param string $allowed_extensions
     * @param int $upload_type
     * @return FileUploadEditorConfiguration
     */
    public static function create(?int $maximum_size, ?string $allowed_extensions, int $upload_type) : FileUploadEditorConfiguration {
        $object = new FileUploadEditorConfiguration();
        $object->maximum_size = $maximum_size;
        $object->allowed_extensions = $allowed_extensions;
        $object->upload_type = $upload_type;
        return $object;
    }
    
    /**
     * @return int|NULL
     */
    public function getMaximumSize() : ?int {
        return $this->maximum_size;
    }
    
    /**
     * @return string|NULL
     */
    public function getAllowedExtensions() : ?string {
        return $this->allowed_extensions;
    }
    
    /**
     * @return int
     */
    public function getUploadType() : int {
        return $this->upload_type;
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var FileUploadEditorConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->maximum_size === $other->maximum_size &&
               $this->amount_of_files === $other->amount_of_files &&
               $this->allowed_extensions === $other->allowed_extensions;
    }
}