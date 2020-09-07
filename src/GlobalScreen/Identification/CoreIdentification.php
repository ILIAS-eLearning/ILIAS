<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class CoreIdentification
 *
 * @see    IdentificationFactory
 * This is a implementation of IdentificationInterface for usage in Core
 * components (they will get them through the factory). This a Serializable and
 * will be used to store in database and cache.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreIdentification extends AbstractIdentification implements IdentificationInterface
{
}
