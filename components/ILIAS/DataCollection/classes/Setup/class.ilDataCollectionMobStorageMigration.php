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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Unzip;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use ILIAS\FileUpload\MimeType;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @noinspection AutoloadingIssuesInspection
 */
class ilDataCollectionMobStorageMigration implements \ILIAS\Setup\Migration
{
    public const DEFAULT_AMOUNT_OF_STEPS = 10000;

    protected ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of DataCollection Mob Objects to native Resource Storage files.";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(\ILIAS\Setup\Environment $environment): array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(\ILIAS\Setup\Environment $environment): void
    {
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilDataCollectionStakeholder(),
            $environment
        );
    }

    public function step(\ILIAS\Setup\Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $manager = $this->helper->getManager();
        $old_stakeholder = new ilMobStakeholder();

        $st = $db->queryF(
            'SELECT il_dcl_stloc1_value.id as id, il_dcl_stloc1_value.value as rid FROM il_dcl_field ' .
            'INNER JOIN il_dcl_record_field ON il_dcl_record_field.field_id = il_dcl_field.id ' .
            'INNER JOIN il_dcl_stloc1_value ON il_dcl_stloc1_value.record_field_id = il_dcl_record_field.id ' .
            'WHERE il_dcl_field.datatype_id = %s',
            [ilDBConstants::T_INTEGER],
            [ilDclDatatype::INPUTFORMAT_MOB]
        );

        while ($row = $db->fetchAssoc($st)) {
            $rid = $manager->find($row['rid']);
            if ($rid !== null) {
                $rev = $manager->getCurrentRevision($rid);
                if ($rev !== null) {
                    if (str_ends_with($rev->getInformation()->getMimeType(), 'zip')) {
                        $fs = $this->helper->getResourceBuilder()->extractStream($rev);
                        foreach ((new Unzip(new UnzipOptions(), $fs))->getFileStreams() as $file) {
                            if ($file->getMetadata('uri') !== '.empty' && $file->getMetadata('uri') !== '/mob_vpreview.png') {
                                $name = pathinfo($file->getMetadata('uri'))['basename'];
                                $new_rid = $manager->stream(
                                    Streams::ofFileInsideZIP($fs->getMetadata('uri'), $name),
                                    $this->helper->getStakeholder(),
                                    $name
                                );
                                $db->manipulateF(
                                    "UPDATE il_dcl_stloc1_value SET value = %s WHERE id = %s",
                                    [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
                                    [$new_rid->serialize(), (int) $row['id']]
                                );
                                $manager->remove($rid, $old_stakeholder);
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $st = $this->helper->getDatabase()->queryF(
            'SELECT il_dcl_stloc1_value.value as rid FROM il_dcl_field ' .
            'INNER JOIN il_dcl_record_field ON il_dcl_record_field.field_id = il_dcl_field.id ' .
            'INNER JOIN il_dcl_stloc1_value ON il_dcl_stloc1_value.record_field_id = il_dcl_record_field.id ' .
            'WHERE il_dcl_field.datatype_id = %s',
            [ilDBConstants::T_INTEGER],
            [ilDclDatatype::INPUTFORMAT_MOB]
        );

        $i = 0;
        while ($row = $this->helper->getDatabase()->fetchAssoc($st)) {
            $rid = $this->helper->getManager()->find($row['rid']);
            if ($rid !== null) {
                $rev = $this->helper->getManager()->getCurrentRevision($rid);
                if ($rev !== null) {
                    if (str_ends_with($rev->getInformation()->getMimeType(), 'zip')) {
                        $i++;
                    }
                }
            }
        }

        return $i;
    }
}
