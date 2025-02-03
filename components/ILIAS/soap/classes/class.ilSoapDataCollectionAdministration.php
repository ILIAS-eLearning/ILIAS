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
 * Soap data-collection administration methods
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilSoapDataCollectionAdministration extends ilSoapAdministration
{
    /**
     * Export DataCollection async
     * @return soap_fault|SoapFault|null|bool
     */
    public function exportDataCollectionContent(
        string $sid,
        int $target_ref_id,
        ?int $table_id = null,
        string $format = ilDclContentExporter::EXPORT_EXCEL,
        ?string $filepath = null
    ) {
        $this->initAuth($sid);
        $this->initIlias();
        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        if (!$target_obj = new ilObjDataCollection($target_ref_id)) {
            return $this->raiseError('No valid target given.', 'CLIENT');
        }

        if (ilObject::_isInTrash($target_ref_id)) {
            return $this->raiseError(
                "Parent with ID $target_ref_id has been deleted.",
                'CLIENT_TARGET_DELETED'
            );
        }

        if (!ilObjDataCollectionAccess::hasReadAccess($target_ref_id)) {
            return $this->raiseError(
                'Check access failed. No permission to read DataCollection',
                "CLIENT_PERMISSION_ISSUE"
            );
        }

        try {
            $exporter = new ilDclContentExporter($target_ref_id, $table_id);
            return $exporter->export($format, $filepath);
        } catch (ilException $exception) {
            return $this->raiseError($exception->getMessage(), $exception->getCode());
        }
    }
}
