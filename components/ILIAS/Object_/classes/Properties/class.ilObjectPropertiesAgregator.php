<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Description of class
 *
 * @author Stephan Kergomard
 */
class ilObjectPropertiesAgregator
{
    public function __construct(
        private ilObjectCorePropertiesRepository $core_properties_repository,
        private ilObjectAdditionalPropertiesRepository $additional_properties_repository
    ) {
    }

    public function getFor(int $object_id): ilObjectProperties
    {
        return new ilObjectProperties(
            $this->core_properties_repository->getFor($object_id),
            $this->core_properties_repository,
            $this->additional_properties_repository->getFor($object_id),
            $this->additional_properties_repository
        );
    }
}
