<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Field\InputInternal;

/**
 * This describes file field.
 */
interface File extends FileUpload, HasDynamicInputs, InputInternal
{

}
