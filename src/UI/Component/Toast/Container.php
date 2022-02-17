<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Toast;

use ILIAS\UI\Component\Component;

/**
 * Interface Container
 * @package ILIAS\UI\Component\Toast
 */
interface Container extends Component
{
    /**
     * @return Toast[]
     */
    public function getToasts() : array;

    public function withAdditionalToast(Toast $toast) : Container;

    public function withoutToasts() : Container;
}
