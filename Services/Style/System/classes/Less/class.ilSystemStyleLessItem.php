<?php
/**
 * Abstracts content of a less file. Currently we have Variable, Category and Comment (random content) as instances.
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
abstract class ilSystemStyleLessItem
{
    abstract public function __toString();
}
