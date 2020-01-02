<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

/**
 * Class DeliveryMethod
 *
 * This interface provides all existing delivery types.
 * Only the types provided by this interface are valid for use with the FileDeliveryTypeFactory.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0
 * @since 5.3
 *
 * @Internal
 */
interface DeliveryMethod
{
    const PHP = 'php';
    const PHP_CHUNKED = 'php_chunked';
    const XACCEL = 'x-accel-redirect';
    const XSENDFILE = 'mod_xsendfile';
}
