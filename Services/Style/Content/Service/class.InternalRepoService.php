<?php declare(strict_types = 1);

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

namespace ILIAS\Style\Content;

use ILIAS\Filesystem;
use ILIAS\FileUpload\FileUpload;
use ilDBInterface;

/**
 * Content style internal repo service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalRepoService
{
    protected ilDBInterface $db;
    protected InternalDataService $data_factory;
    protected ColorDBRepo $color_repo;
    protected CharacteristicDBRepo $characteristic_repo;
    protected CharacteristicCopyPasteSessionRepo $characteristic_copy_paste_repo;
    protected ImageFileRepo $image_repo;
    protected FileUpload $upload;

    public function __construct(
        InternalDataService $data_factory,
        ilDBInterface $db,
        Filesystem\Filesystem $web_files,
        FileUpload $upload
    ) {
        $this->db = $db;
        $this->data_factory = $data_factory;
        $this->upload = $upload;

        $this->color_repo = new ColorDBRepo(
            $db,
            $data_factory
        );
        $this->characteristic_repo = new CharacteristicDBRepo(
            $db,
            $data_factory
        );
        $this->image_repo = new ImageFileRepo(
            $data_factory,
            $web_files,
            $upload
        );
        $this->characteristic_copy_paste_repo =
            new CharacteristicCopyPasteSessionRepo();
    }

    public function characteristic(
    ) : CharacteristicDBRepo {
        return $this->characteristic_repo;
    }

    public function characteristicCopyPaste(
    ) : CharacteristicCopyPasteSessionRepo {
        return $this->characteristic_copy_paste_repo;
    }

    public function color() : ColorDBRepo
    {
        return $this->color_repo;
    }

    public function image() : ImageFileRepo
    {
        return $this->image_repo;
    }

    public function repositoryContainer() : Container\ContainerDBRepository
    {
        return new Container\ContainerDBRepository(
            $this->db
        );
    }

    /**
     * Objects without ref id (e.g. portfolios) can use
     * the manager with a ref_id of 0, e.g. to get selectable styles
     */
    public function object() : Object\ObjectDBRepository
    {
        return new Object\ObjectDBRepository(
            $this->db
        );
    }
}
