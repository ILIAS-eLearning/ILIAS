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

require_once('vendor/composer/vendor/autoload.php');

class ilSystemStyleStyleScssFileTest extends ilSystemStyleBaseFS
{
    protected function setUp(): void
    {
        parent::setUp();
        $path = $this->container->getScssSettingsPath($this->style->getId());
        $this->file = new ilSystemStyleScssSettingsFile($path, "variables1.scss");
    }

    public function testConstructAndRead(): void
    {
        $this->assertCount(14, $this->file->getItems());
    }

    public function testReadCorrectTypes(): void
    {
        $this->assertCount(2, $this->file->getCategories());
        $this->assertCount(6, $this->file->getVariables());
        $this->assertCount(14, $this->file->getItems());
    }

    public function testGetCategory(): void
    {
        $expected_categories = [];

        $expected_categories['Category 11'] = new ilSystemStyleScssCategory('Category 11', 'Comment Category 11');
        $expected_categories['Category 12'] = new ilSystemStyleScssCategory('Category 12', 'Comment Category 12');

        $this->assertEquals($expected_categories, $this->file->getCategories());
    }

    public function testGetItems(): void
    {
        $expected_category11 = new ilSystemStyleScssCategory('Category 11', 'Comment Category 11');
        $expected_comment12 = new ilSystemStyleScssComment('// Random Section 11');
        $expected_comment13 = new ilSystemStyleScssComment('');
        $expected_variable111 = new ilSystemStyleScssVariable(
            'variable111',
            'value111',
            'comment variable 111',
            'Category 11',
            []
        );
        $expected_variable112 = new ilSystemStyleScssVariable(
            'variable112',
            'value112',
            'comment variable 112',
            'Category 11',
            []
        );
        $expected_variable113 = new ilSystemStyleScssVariable(
            'variable113',
            '$variable111',
            'comment variable 113',
            'Category 11',
            ['variable111']
        );
        $expected_comment14 = new ilSystemStyleScssComment('');
        $expected_category12 = new ilSystemStyleScssCategory('Category 12', 'Comment Category 12');
        $expected_comment16 = new ilSystemStyleScssComment('/**');
        $expected_comment17 = new ilSystemStyleScssComment(' Random Section 12 **/');
        $expected_comment18 = new ilSystemStyleScssComment('');
        $expected_variable121 = new ilSystemStyleScssVariable(
            'variable121',
            '$variable111',
            'comment variable 121',
            'Category 12',
            ['variable111']
        );
        $expected_variable122 = new ilSystemStyleScssVariable(
            'variable122',
            'value121',
            'comment variable 122',
            'Category 12',
            []
        );
        $expected_variable123 = new ilSystemStyleScssVariable(
            'variable123',
            '$variable121',
            'comment variable 123',
            'Category 12',
            ['variable121']
        );

        $expected_items = [
            $expected_category11,
            $expected_comment12,
            $expected_comment13,
            $expected_variable111,
            $expected_variable112,
            $expected_variable113,
            $expected_comment14,
            $expected_category12,
            $expected_comment16,
            $expected_comment17,
            $expected_comment18,
            $expected_variable121,
            $expected_variable122,
            $expected_variable123,
        ];

        $this->assertEquals($expected_items, $this->file->getItems());
    }

    public function testGetContent(): void
    {
        $expected_content = file_get_contents($this->file->getScssVariablesSettingsPath()."/variables1.scss");
        $this->assertEquals($expected_content, $this->file->getContent());
    }

    public function testAddAndWriteItems(): void
    {
        $empty_path = $this->container->getSkinDirectory() . 'scss-test/empty-file/';
        $file = new ilSystemStyleScssSettingsFile($empty_path, "empty.scss");

        $expected_category1 = new ilSystemStyleScssCategory('Category 1', 'Comment Category 1');
        $expected_comment2 = new ilSystemStyleScssComment('// Random Section 1');
        $expected_comment3 = new ilSystemStyleScssComment('');
        $expected_variable11 = new ilSystemStyleScssVariable(
            'variable11',
            'value11',
            'comment variable 11',
            'Category 1',
            []
        );
        $expected_variable12 = new ilSystemStyleScssVariable(
            'variable12',
            'value12',
            'comment variable 12',
            'Category 1',
            []
        );
        $expected_variable13 = new ilSystemStyleScssVariable(
            'variable13',
            '$variable11',
            'comment variable 13',
            'Category 1',
            ['variable11']
        );
        $expected_comment4 = new ilSystemStyleScssComment('');
        $expected_category2 = new ilSystemStyleScssCategory('Category 2', 'Comment Category 2');
        $expected_comment6 = new ilSystemStyleScssComment('/**');
        $expected_comment7 = new ilSystemStyleScssComment(' Random Section 2 **/');
        $expected_comment8 = new ilSystemStyleScssComment('');
        $expected_variable21 = new ilSystemStyleScssVariable(
            'variable21',
            '$variable11',
            'comment variable 21',
            'Category 2',
            ['variable11']
        );
        $expected_variable22 = new ilSystemStyleScssVariable(
            'variable22',
            'value21',
            'comment variable 22',
            'Category 2',
            []
        );
        $expected_variable23 = new ilSystemStyleScssVariable(
            'variable23',
            '$variable21',
            'comment variable 23',
            'Category 2',
            ['variable21']
        );

        $expected_items = [$expected_category1,
                           $expected_comment2,
                           $expected_comment3,
                           $expected_variable11,
                           $expected_variable12,
                           $expected_variable13,
                           $expected_comment4,
                           $expected_category2,
                           $expected_comment6,
                           $expected_comment7,
                           $expected_comment8,
                           $expected_variable21,
                           $expected_variable22,
                           $expected_variable23
        ];

        foreach ($expected_items as $item) {
            $file->addItem($item);
        }
        $file->write();

        $new_file = new ilSystemStyleScssSettingsFile($empty_path, "empty.scss");
        $this->assertEquals($expected_items, $new_file->getItems());
    }
}
