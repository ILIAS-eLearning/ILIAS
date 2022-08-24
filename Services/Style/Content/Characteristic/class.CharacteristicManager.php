<?php

declare(strict_types=1);

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

use ILIAS\Style\Content\Access;
use ilObjUser;
use ilObjStyleSheet;

/**
 * Main business logic for characteristics
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicManager
{
    protected CharacteristicDBRepo $repo;
    protected ColorDBRepo $color_repo;
    protected ilObjUser $user;
    protected Access\StyleAccessManager $access_manager;
    protected CharacteristicCopyPasteSessionRepo $session;
    protected int $style_id;

    public function __construct(
        int $style_id,
        Access\StyleAccessManager $access_manager,
        CharacteristicDBRepo $char_repo,
        CharacteristicCopyPasteSessionRepo $char_copy_paste_repo,
        ColorDBRepo $color_repo,
        ilObjUser $user
    ) {
        $this->user = $user;
        $this->repo = $char_repo;
        $this->color_repo = $color_repo;
        $this->access_manager = $access_manager;
        $this->session = $char_copy_paste_repo;
        $this->style_id = $style_id;
    }

    public function addCharacteristic(
        string $type,
        string $char,
        bool $hidden = false
    ): void {
        $this->repo->addCharacteristic(
            $this->style_id,
            $type,
            $char,
            $hidden
        );

        ilObjStyleSheet::_writeUpToDate($this->style_id, false);
    }

    /**
     * Check if characteristic exists
     */
    public function exists(
        string $type,
        string $char
    ): bool {
        return $this->repo->exists(
            $this->style_id,
            $type,
            $char
        );
    }

    /**
     * Get characteristic by key
     */
    public function getByKey(
        string $type,
        string $characteristic
    ): ?Characteristic {
        return $this->repo->getByKey(
            $this->style_id,
            $type,
            $characteristic
        );
    }

    /**
     * Get characteristics by type
     */
    public function getByType(
        string $type
    ): array {
        return $this->repo->getByType(
            $this->style_id,
            $type
        );
    }

    /**
     * Get characteristics by type
     */
    public function getByTypes(
        array $types,
        bool $include_hidden = true,
        bool $include_outdated = true
    ): array {
        return $this->repo->getByTypes(
            $this->style_id,
            $types,
            $include_hidden,
            $include_outdated
        );
    }

    /**
     * Get characteristics by supertype
     */
    public function getBySuperType(
        string $supertype
    ): array {
        return $this->repo->getBySuperType(
            $this->style_id,
            $supertype
        );
    }

    /**
     * Get characteristic by key
     */
    public function getPresentationTitle(
        string $type,
        string $characteristic,
        bool $fallback_to_characteristic = true
    ): string {
        $char = $this->repo->getByKey(
            $this->style_id,
            $type,
            $characteristic
        );

        $titles = $char ? $char->getTitles() : [];

        $lang = $this->user->getLanguage();

        if (($titles[$lang] ?? "") != "") {
            return $titles[$lang];
        }
        if ($fallback_to_characteristic) {
            return $characteristic;
        }
        return "";
    }

    /**
     * Save titles for characteristic
     * @throws ContentStyleNoPermissionException
     */
    public function saveTitles(
        string $type,
        string $characteristic,
        array $titles
    ): void {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }
        $this->repo->saveTitles(
            $this->style_id,
            $type,
            $characteristic,
            $titles
        );
    }

    /**
     * Save characteristic hidden status
     */
    public function saveHidden(
        string $type,
        string $characteristic,
        bool $hide
    ): void {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }
        $this->repo->saveHidden(
            $this->style_id,
            $type,
            $characteristic,
            $hide
        );
    }

    /**
     * Save characteristic outdated status
     */
    public function saveOutdated(
        string $type,
        string $characteristic,
        bool $outdated
    ): void {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }
        $this->repo->saveOutdated(
            $this->style_id,
            $type,
            $characteristic,
            $outdated
        );
    }

    /**
     * Save characteristics order
     * @param array $order_nrs (key is characteristic value is order nr)
     */
    public function saveOrderNrs(
        string $type,
        array $order_nrs
    ): void {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }

        asort($order_nrs, SORT_NUMERIC);

        foreach ($order_nrs as $char => $nr) {
            $this->repo->saveOrderNr(
                $this->style_id,
                $type,
                $char,
                $nr
            );
        }
    }


    /**
     * Delete Characteristic
     * @throws ContentStyleNoPermissionException
     */
    public function deleteCharacteristic(
        string $type,
        string $class
    ): void {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }
        $tag = ilObjStyleSheet::_determineTag($type);

        // check, if characteristic is not a core style
        $core_styles = ilObjStyleSheet::_getCoreStyles();
        if (empty($core_styles[$type . "." . $tag . "." . $class])) {
            $this->repo->deleteCharacteristic(
                $this->style_id,
                $type,
                $tag,
                $class
            );
        }

        ilObjStyleSheet::_writeUpToDate($this->style_id, false);
    }

    public function setCopyCharacteristics(
        string $style_type,
        array $characteristics
    ): void {
        $this->session->set($this->style_id, $style_type, $characteristics);
    }

    /**
     * Is in copy process?
     */
    public function hasCopiedCharacteristics(string $style_type): bool
    {
        return $this->session->hasEntries($style_type);
    }

    public function clearCopyCharacteristics(): void
    {
        $this->session->clear();
    }

    public function getCopyCharacteristicStyleId(): int
    {
        $data = $this->session->getData();
        return $data->style_id;
    }

    public function getCopyCharacteristicStyleType(): string
    {
        $data = $this->session->getData();
        return $data->style_type;
    }

    public function getCopyCharacteristics(): array
    {
        $data = $this->session->getData();
        return $data->characteristics;
    }

    /**
     * Copy characteristic
     * @throws ContentStyleNoPermissionException
     */
    public function copyCharacteristicFromSource(
        int $source_style_id,
        string $source_style_type,
        string $source_char,
        string $new_char,
        array $new_titles
    ): void {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }

        if ($this->exists($source_style_type, $new_char)) {
            $target_char = $this->getByKey($source_style_type, $new_char);
            if (count($new_titles) == 0) {
                $new_titles = $target_char->getTitles();
            }
            $this->deleteCharacteristic($source_style_type, $new_char);
        }

        $this->addCharacteristic($source_style_type, $new_char);
        $this->saveTitles($source_style_type, $new_char, $new_titles);

        $from_style = new ilObjStyleSheet($source_style_id);

        // todo fix using mq_id
        $pars = $from_style->getParametersOfClass($source_style_type, $source_char);

        $colors = array();
        foreach ($pars as $p => $v) {
            if (substr($v, 0, 1) == "!") {
                $colors[] = substr($v, 1);
            }
            $this->replaceParameter(
                ilObjStyleSheet::_determineTag($source_style_type),
                $new_char,
                $p,
                $v,
                $source_style_type
            );
        }

        // copy colors
        foreach ($colors as $c) {
            if (!$this->color_repo->colorExists($this->style_id, $c)) {
                $this->color_repo->addColor(
                    $this->style_id,
                    $c,
                    $from_style->getColorCodeForName($c)
                );
            }
        }
    }

    public function replaceParameter(
        string $a_tag,
        string $a_class,
        string $a_par,
        string $a_val,
        string $a_type,
        int $a_mq_id = 0,
        bool $a_custom = false
    ): void {
        if ($a_val != "") {
            $this->repo->replaceParameter(
                $this->style_id,
                $a_tag,
                $a_class,
                $a_par,
                $a_val,
                $a_type,
                $a_mq_id,
                $a_custom
            );
        } else {
            $this->deleteParameter(
                $a_tag,
                $a_class,
                $a_par,
                $a_type,
                $a_mq_id,
                $a_custom
            );
        }
    }

    public function deleteParameter(
        string $a_tag,
        string $a_class,
        string $a_par,
        string $a_type,
        int $a_mq_id = 0,
        bool $a_custom = false
    ): void {
        $this->repo->deleteParameter(
            $this->style_id,
            $a_tag,
            $a_class,
            $a_par,
            $a_type,
            $a_mq_id,
            $a_custom
        );
    }
}
