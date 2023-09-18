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

/**
 * Interface ilBiblFactoryFacadeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFactoryFacadeInterface
{
    public function typeFactory(): \ilBiblTypeFactoryInterface;

    public function overviewModelFactory(): \ilBiblOverviewModelFactoryInterface;

    public function type(): \ilBiblTypeInterface;

    public function libraryFactory(): \ilBiblLibraryFactoryInterface;

    public function fieldFactory(): \ilBiblFieldFactoryInterface;

    public function translationFactory(): \ilBiblTranslationFactoryInterface;

    public function entryFactory(): \ilBiblEntryFactoryInterface;

    public function fileReaderFactory(): \ilBiblFileReaderFactoryInterface;

    public function filterFactory(): \ilBiblFieldFilterFactoryInterface;

    public function attributeFactory(): \ilBiblAttributeFactoryInterface;

    public function iliasObjId(): int;

    public function iliasRefId(): int;

    public function dataFactory(): \ilBiblDataFactoryInterface;
}
