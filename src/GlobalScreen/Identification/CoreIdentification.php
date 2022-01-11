<?php namespace ILIAS\GlobalScreen\Identification;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class CoreIdentification
 * @see    IdentificationFactory
 * This is a implementation of IdentificationInterface for usage in Core
 * components (they will get them through the factory). This a Serializable and
 * will be used to store in database and cache.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreIdentification extends AbstractIdentification implements IdentificationInterface
{
}
