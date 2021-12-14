<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Field\InputInternal;

/**
 * This describes file field.
 */
interface File extends FileUploadAware, DynamicInputsAware, InputInternal
{

}
