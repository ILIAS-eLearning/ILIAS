<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This interface must be implemented by all Inputs that support
 * Filter Containers.
 *
 * These inputs need to implement an additional rendering in the
 * FilterContextRenderer and provide the 'getUpdateOnLoadCode' method that allows
 * the Filter to show the current selected values within the Filter component.
 *
 * @author killing@leifos.de
 */
interface FilterInput extends Input
{
}
