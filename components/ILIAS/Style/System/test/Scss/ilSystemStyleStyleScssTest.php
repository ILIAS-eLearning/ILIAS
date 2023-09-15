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

require_once('libs/composer/vendor/autoload.php');

class ilSystemStyleStyleScssTest extends ilSystemStyleBaseFS
{
    public function testConstructAndRead(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));
        $this->assertCount(32, $file->getItems());
    }

    public function testReadCorrectTypes(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));

        $this->assertCount(5, $file->getCategories());
        $this->assertCount(12, $file->getVariables());
        $this->assertCount(32, $file->getItems());
    }

    public function testGetVariableByName(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));

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
        $expected_variable211 = new ilSystemStyleScssVariable(
            'variable211',
            'value211',
            'comment variable 211',
            'Category 21',
            []
        );
        $expected_variable212 = new ilSystemStyleScssVariable(
            'variable212',
            'value212',
            'comment variable 212',
            'Category 21',
            []
        );
        $expected_variable213 = new ilSystemStyleScssVariable(
            'variable213',
            '$variable211',
            'comment variable 213',
            'Category 21',
            ['variable211']
        );

        $expected_variable221 = new ilSystemStyleScssVariable(
            'variable221',
            '$variable211',
            'comment variable 221',
            'Category 22',
            ['variable211']
        );
        $expected_variable222 = new ilSystemStyleScssVariable(
            'variable222',
            'value221',
            'comment variable 222',
            'Category 22',
            []
        );
        $expected_variable223 = new ilSystemStyleScssVariable(
            'variable223',
            '$variable221',
            'comment variable 223',
            'Category 22',
            ['variable221']
        );
        $this->assertEquals($expected_variable111, $file->getVariableByName('variable111'));
        $this->assertEquals($expected_variable112, $file->getVariableByName('variable112'));
        $this->assertEquals($expected_variable113, $file->getVariableByName('variable113'));

        $this->assertEquals($expected_variable121, $file->getVariableByName('variable121'));
        $this->assertEquals($expected_variable122, $file->getVariableByName('variable122'));
        $this->assertEquals($expected_variable123, $file->getVariableByName('variable123'));

        $this->assertEquals($expected_variable211, $file->getVariableByName('variable211'));
        $this->assertEquals($expected_variable212, $file->getVariableByName('variable212'));
        $this->assertEquals($expected_variable213, $file->getVariableByName('variable213'));

        $this->assertEquals($expected_variable221, $file->getVariableByName('variable221'));
        $this->assertEquals($expected_variable222, $file->getVariableByName('variable222'));
        $this->assertEquals($expected_variable223, $file->getVariableByName('variable223'));
    }

    public function testGetCategory(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));
        $expected_categories = [];

        $expected_categories['Category 11'] = new ilSystemStyleScssCategory('Category 11', 'Comment Category 11');
        $expected_categories['Category 12'] = new ilSystemStyleScssCategory('Category 12', 'Comment Category 12');
        $expected_categories['Category 21'] = new ilSystemStyleScssCategory('Category 21', 'Comment Category 21');
        $expected_categories['Category 22'] = new ilSystemStyleScssCategory('Category 22', 'Comment Category 22');
        $expected_categories['Settings'] = new ilSystemStyleScssCategory('Settings', 'Settings Comment');

        $this->assertEquals($expected_categories, $file->getCategories());
    }

    public function testGetItems(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));

        $expected_settings = new ilSystemStyleScssCategory('Settings', 'Settings Comment');
        $expected_settings_comment1 = new ilSystemStyleScssComment('');
        $expected_settings_comment2 = new ilSystemStyleScssComment('@forward "./variables1";');
        $expected_settings_comment3 = new ilSystemStyleScssComment('@forward "./variables2";');
        $expected_category21 = new ilSystemStyleScssCategory('Category 21', 'Comment Category 21');
        $expected_comment22 = new ilSystemStyleScssComment('// Random Section 21');
        $expected_comment23 = new ilSystemStyleScssComment('');
        $expected_variable211 = new ilSystemStyleScssVariable(
            'variable211',
            'value211',
            'comment variable 211',
            'Category 21',
            []
        );
        $expected_variable212 = new ilSystemStyleScssVariable(
            'variable212',
            'value212',
            'comment variable 212',
            'Category 21',
            []
        );
        $expected_variable213 = new ilSystemStyleScssVariable(
            'variable213',
            '$variable211',
            'comment variable 213',
            'Category 21',
            ['variable211']
        );
        $expected_comment24 = new ilSystemStyleScssComment('');
        $expected_category22 = new ilSystemStyleScssCategory('Category 22', 'Comment Category 22');
        $expected_comment26 = new ilSystemStyleScssComment('/**');
        $expected_comment27 = new ilSystemStyleScssComment(' Random Section 212 **/');
        $expected_comment28 = new ilSystemStyleScssComment('');
        $expected_variable221 = new ilSystemStyleScssVariable(
            'variable221',
            '$variable211',
            'comment variable 221',
            'Category 22',
            ['variable211']
        );
        $expected_variable222 = new ilSystemStyleScssVariable(
            'variable222',
            'value221',
            'comment variable 222',
            'Category 22',
            []
        );
        $expected_variable223 = new ilSystemStyleScssVariable(
            'variable223',
            '$variable221',
            'comment variable 223',
            'Category 22',
            ['variable221']
        );

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
            $expected_settings,
            $expected_settings_comment1,
            $expected_settings_comment2,
            $expected_settings_comment3,
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
            $expected_category21,
            $expected_comment22,
            $expected_comment23,
            $expected_variable211,
            $expected_variable212,
            $expected_variable213,
            $expected_comment24,
            $expected_category22,
            $expected_comment26,
            $expected_comment27,
            $expected_comment28,
            $expected_variable221,
            $expected_variable222,
            $expected_variable223
        ];

        $this->assertEquals($expected_items, $file->getItems());
    }

    public function testGetContent(): void
    {
        $settings = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));
        $expected_content = $this->getAllContentOfFolder($this->container->getScssSettingsPath($this->style->getId()));
        $this->assertEquals($expected_content, $settings->getContent());
    }

    public function testGetVariablesForDelosOverride(): void
    {
        $settings = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));
        $expected_content = '$variable111: globals.$variable111,
$variable112: globals.$variable112,
$variable113: globals.$variable113,
$variable121: globals.$variable121,
$variable122: globals.$variable122,
$variable123: globals.$variable123,
$variable211: globals.$variable211,
$variable212: globals.$variable212,
$variable213: globals.$variable213,
$variable221: globals.$variable221,
$variable222: globals.$variable222,
$variable223: globals.$variable223,
';
        $this->assertEquals($expected_content, $settings->getVariablesForDelosOverride());
    }

    public function testReadWriteDouble(): void
    {
        $expected_content = $this->getAllContentOfFolder($this->container->getScssSettingsPath($this->style->getId()));

        $settings = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));
        $settings->write();
        $settings = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));
        $settings->write();
        $settings = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));

        $this->assertEquals($expected_content, $settings->getContent());
    }

    public function testReadWriteDoubleRealFolderSCSS(): void
    {
        $expected_content = $this->getAllContentOfFolder($this->container->getSkinDirectory() . 'scss-test/real-folder');

        $settings = new ilSystemStyleScssSettings($this->container->getSkinDirectory() . 'scss-test/real-folder');
        $settings->write();
        $settings = new ilSystemStyleScssSettings($this->container->getSkinDirectory() . 'scss-test/real-folder');
        $settings->write();
        $settings = new ilSystemStyleScssSettings($this->container->getSkinDirectory() . 'scss-test/real-folder');

        // !defaults will get removed in this process
        $expected_content = str_replace(" !default", "", $expected_content);
        $this->assertEquals($expected_content, $settings->getContent());
    }

    public function testChangeVariable(): void
    {
        $settings = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));
        $variable = $settings->getVariableByName('variable111');
        $variable->setValue('newvalue111');

        $expected_settings = new ilSystemStyleScssCategory('Settings', 'Settings Comment');
        $expected_settings_comment1 = new ilSystemStyleScssComment('');
        $expected_settings_comment2 = new ilSystemStyleScssComment('@forward "./variables1";');
        $expected_settings_comment3 = new ilSystemStyleScssComment('@forward "./variables2";');
        $expected_category11 = new ilSystemStyleScssCategory('Category 11', 'Comment Category 11');
        $expected_comment12 = new ilSystemStyleScssComment('// Random Section 11');
        $expected_comment13 = new ilSystemStyleScssComment('');
        $expected_variable111 = new ilSystemStyleScssVariable(
            'variable111',
            'newvalue111',
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

        $expected_category21 = new ilSystemStyleScssCategory('Category 21', 'Comment Category 21');
        $expected_comment22 = new ilSystemStyleScssComment('// Random Section 21');
        $expected_comment23 = new ilSystemStyleScssComment('');
        $expected_variable211 = new ilSystemStyleScssVariable(
            'variable211',
            'value211',
            'comment variable 211',
            'Category 21',
            []
        );
        $expected_variable212 = new ilSystemStyleScssVariable(
            'variable212',
            'value212',
            'comment variable 212',
            'Category 21',
            []
        );
        $expected_variable213 = new ilSystemStyleScssVariable(
            'variable213',
            '$variable211',
            'comment variable 213',
            'Category 21',
            ['variable211']
        );
        $expected_comment24 = new ilSystemStyleScssComment('');
        $expected_category22 = new ilSystemStyleScssCategory('Category 22', 'Comment Category 22');
        $expected_comment26 = new ilSystemStyleScssComment('/**');
        $expected_comment27 = new ilSystemStyleScssComment(' Random Section 212 **/');
        $expected_comment28 = new ilSystemStyleScssComment('');
        $expected_variable221 = new ilSystemStyleScssVariable(
            'variable221',
            '$variable211',
            'comment variable 221',
            'Category 22',
            ['variable211']
        );
        $expected_variable222 = new ilSystemStyleScssVariable(
            'variable222',
            'value221',
            'comment variable 222',
            'Category 22',
            []
        );
        $expected_variable223 = new ilSystemStyleScssVariable(
            'variable223',
            '$variable221',
            'comment variable 223',
            'Category 22',
            ['variable221']
        );


        $expected_items = [
            $expected_settings,
            $expected_settings_comment1,
            $expected_settings_comment2,
            $expected_settings_comment3,
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
            $expected_category21,
            $expected_comment22,
            $expected_comment23,
            $expected_variable211,
            $expected_variable212,
            $expected_variable213,
            $expected_comment24,
            $expected_category22,
            $expected_comment26,
            $expected_comment27,
            $expected_comment28,
            $expected_variable221,
            $expected_variable222,
            $expected_variable223
        ];

        $this->assertEquals($expected_items, $settings->getItems());
    }


    public function testGetVariableReferences(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));

        $this->assertEquals(['variable113', 'variable121'], $file->getReferencesToVariable('variable111'));
        $this->assertEquals([], $file->getReferencesToVariable('variable112'));
        $this->assertEquals([], $file->getReferencesToVariable('variable113'));

        $this->assertEquals(['variable123'], $file->getReferencesToVariable('variable121'));
        $this->assertEquals([], $file->getReferencesToVariable('variable122'));
        $this->assertEquals([], $file->getReferencesToVariable('variable123'));
    }

    public function testGetRefAndCommentAsString(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));

        $this->assertEquals(
            'comment variable 111</br>Usage: variable113; variable121; ',
            $file->getRefAndCommentAsString('variable111', 'Usage:')
        );
        $this->assertEquals('comment variable 112', $file->getRefAndCommentAsString('variable112', 'Usage:'));
        $this->assertEquals('comment variable 113', $file->getRefAndCommentAsString('variable113', 'Usage:'));

        $this->assertEquals('comment variable 121</br>Usage: variable123; ', $file->getRefAndCommentAsString('variable121', 'Usage:'));
        $this->assertEquals('comment variable 122', $file->getRefAndCommentAsString('variable122', 'Usage:'));
        $this->assertEquals('comment variable 123', $file->getRefAndCommentAsString('variable123', 'Usage:'));
    }

    public function testGetVariableReferencesAsString(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getScssSettingsPath($this->style->getId()));

        $this->assertEquals('variable113; variable121; ', $file->getReferencesToVariableAsString('variable111'));
        $this->assertEquals('', $file->getReferencesToVariableAsString('variable112'));
        $this->assertEquals('', $file->getReferencesToVariableAsString('variable113'));

        $this->assertEquals('variable123; ', $file->getReferencesToVariableAsString('variable121'));
        $this->assertEquals('', $file->getReferencesToVariableAsString('variable122'));
        $this->assertEquals('', $file->getReferencesToVariableAsString('variable123'));
    }

    public function testReadCorrectTypesEdgeCases(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getSkinDirectory() . 'scss-test/edge-cases');

        $this->assertCount(3, $file->getCategories());
        $this->assertCount(8, $file->getVariables());
        $this->assertCount(15, $file->getItems());
    }

    public function testGetItemsEdgeCases(): void
    {
        $file = new ilSystemStyleScssSettings($this->container->getSkinDirectory() . 'scss-test/edge-cases');

        $expected_comment1 = new ilSystemStyleScssComment('// No Category to start');
        $expected_comment2 = new ilSystemStyleScssComment('');

        $expected_variable11 = new ilSystemStyleScssVariable(
            'variableNoCategory1',
            'value11',
            'comment variable 11',
            '',
            []
        );
        $expected_variable12 = new ilSystemStyleScssVariable('variableNoCategory1NoComment', 'value12', '', '', []);

        $expected_category1 = new ilSystemStyleScssCategory('Category 1 no valid section', '');

        $expected_variable21 = new ilSystemStyleScssVariable(
            'variableNoValidSection1',
            'value21',
            '',
            'Category 1 no valid section',
            []
        );
        $expected_variable22 = new ilSystemStyleScssVariable(
            'variableNoValidSection2',
            'value22',
            'comment',
            'Category 1 no valid section',
            []
        );

        $expected_comment3 = new ilSystemStyleScssComment('');

        $expected_category2 = new ilSystemStyleScssCategory('Category 2', 'Comment Category 2');

        $expected_variable31 = new ilSystemStyleScssVariable(
            'regular',
            'value',
            'Hard references id',
            'Category 2',
            []
        );
        $expected_variable32 = new ilSystemStyleScssVariable(
            'variable21',
            'floor(($regular * 1.6)) * lighten($regular, 20%)',
            'Hard references',
            'Category 2',
            ['regular']
        );

        $expected_comment4 = new ilSystemStyleScssComment('');

        $expected_category3 = new ilSystemStyleScssCategory('Category 3', 'No Section Between');
        $expected_variable41 = new ilSystemStyleScssVariable('variable3', 'value3', '', 'Category 3', []);
        $expected_variable42 = new ilSystemStyleScssVariable('variable4', 'value4', '!default identifier', 'Category 3', []);

        $expected_items = [$expected_comment1,
                           $expected_comment2,
                           $expected_variable11,
                           $expected_variable12,
                           $expected_category1,
                           $expected_variable21,
                           $expected_variable22,
                           $expected_comment3,
                           $expected_category2,
                           $expected_variable31,
                           $expected_variable32,
                           $expected_comment4,
                           $expected_category3,
                           $expected_variable41,
                           $expected_variable42
        ];

        $this->assertEquals($expected_items, $file->getItems());
    }
}
