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

class ilSystemStyleHTMLExport
{
    private array $images = [];
    protected string $style_dir;
    protected string $style_img_dir;
    protected string $img_dir;
    protected string $img_browser_dir;

    public function __construct(string $a_exp_dir)
    {
        $this->style_dir = $a_exp_dir . '/templates/default';
        $this->style_img_dir = $a_exp_dir . '/templates/default/images';
        $this->img_dir = $a_exp_dir . '/images';
        $this->img_browser_dir = $a_exp_dir . '/images/browser';

        // add standard images
        $this->addImage('enlarge.svg');
        $this->addImage('browser/blank.png', '/browser/plus.png');
        $this->addImage('browser/blank.png', '/browser/minus.png');
        $this->addImage('browser/blank.png', '/browser/blank.png');
        $this->addImage('spacer.png');
        $this->addImage('icon_st.svg');
        $this->addImage('icon_pg.svg');
        $this->addImage('icon_lm.svg');
        $this->addImage('nav_arr_L.png');
        $this->addImage('nav_arr_R.png');
    }

    public function createDirectories() : void
    {
        ilFileUtils::makeDirParents($this->style_dir);
        ilFileUtils::makeDirParents($this->img_dir);
        ilFileUtils::makeDirParents($this->img_browser_dir);
    }

    /**
     * Add (icon) image to the list of images to be exported
     */
    public function addImage(string $a_file, string $a_exp_file_name = '') : void
    {
        $this->images[] = ['file' => $a_file,
                           'exp_file_name' => $a_exp_file_name
        ];
    }

    public function export() : void
    {
        $this->createDirectories();

        // export system style sheet
        $location_stylesheet = ilUtil::getStyleSheetLocation('filesystem');
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(dirname($location_stylesheet), FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($this->style_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
            } else {
                copy($item->getPathname(), $this->style_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
            }
        }

        // export (icon) images
        foreach ($this->images as $im) {
            $from = $to = $im['file'];
            if ($im['exp_file_name'] != '') {
                $to = $im['exp_file_name'];
            }
            copy(
                ilUtil::getImagePath($from, '', 'filesystem'),
                $this->img_dir . '/' . $to
            );
        }
    }
}
