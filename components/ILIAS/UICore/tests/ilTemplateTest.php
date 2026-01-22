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

use PHPUnit\Framework\TestCase;

class ilTemplateTest extends TestCase
{
    protected ilTemplate $il_template;
    protected string $il_root;

    public function setUp(): void
    {
        $this->il_template = new class () extends ilTemplate {
            protected string $style = 'delos';
            protected string $skin = 'default';
            protected bool $file_exists = true;
            public function __construct()
            {
            }
            public function _getTemplatePath(string $a_tplname, ?string $a_in_module = null): string
            {
                return $this->getTemplatePath($a_tplname, $a_in_module);
            }
            protected function getCurrentStyle(): ?string
            {
                return $this->style;
            }
            public function withStyle(string $style)
            {
                $clone = clone $this;
                $clone->style = $style;
                return $clone;
            }
            public function withSkin(string $skin)
            {
                $clone = clone $this;
                $clone->skin = $skin;
                return $clone;
            }
            protected function getCurrentSkin(): ?string
            {
                return $this->skin;
            }
            protected function fileExistsInSkin(string $path): bool
            {
                return $this->file_exists;
            }
            public function withFileExists(bool $file_exists)
            {
                $clone = clone $this;
                $clone->file_exists = $file_exists;
                return $clone;
            }
        };
        $this->il_root = realpath(__DIR__ . '/../../../../');

    }


    public static function templatePathDataProvider(): array
    {
        $il_root = realpath(__DIR__ . '/../../../../');
        return [
            'standard component template' => [
                'skin' => 'default', 'style' => 'delos', 'file_exists' => true,
                'tpl_filename' => 'tpl.appointment_panel.html',
                'component' => 'components/ILIAS/Calendar',
                'expected' => $il_root . '/components/ILIAS/Calendar/templates/default/tpl.appointment_panel.html'
            ],
            'plugin template' => [
                'skin' => 'default', 'style' => 'delos', 'file_exists' => true,
                'tpl_filename' => 'tpl.test.html',
                 //ilPlugin::getDirectory() will return something like this:
                'component' => $il_root . '/components/ILIAS/Component/classes/../../../../public/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/EditorAsMode',
                'expected' => $il_root . '/components/ILIAS/Component/classes/../../../../public/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/EditorAsMode/templates/default/tpl.test.html',
            ],
            'plugin template_file_no_component' => [
                'skin' => 'default', 'style' => 'delos', 'file_exists' => true,
                'tpl_filename' => $il_root . '/public/Customizing/global/plugins/Services/User/UDFDefinition/CascadingSelect/templates/tpl.prop_cascading_select.html',
                'component' => '',
                'expected' => $il_root . '/public/Customizing/global/plugins/Services/User/UDFDefinition/CascadingSelect/templates/tpl.prop_cascading_select.html',
            ],
            'custom skin' => [
                'skin' => 'mySkin', 'style' => 'myStyle', 'file_exists' => true,
                'tpl_filename' => 'tpl.external_settings.html',
                'component' => 'components/ILIAS/Administration',
                'expected' => $il_root . '/public/Customizing/skin/mySkin/myStyle/components/ILIAS/Administration/tpl.external_settings.html',
            ],
            'custom skin, unaltered file' => [
                'skin' => 'mySkin', 'style' => 'myStyle', 'file_exists' => false,
                'tpl_filename' => 'tpl.external_settings.html',
                'component' => 'components/ILIAS/Administration',
                'expected' => $il_root . '/components/ILIAS/Administration/templates/default/tpl.external_settings.html',
            ],
            'ui template' => [
                'skin' => 'default', 'style' => 'delos', 'file_exists' => true,
                'tpl_filename' => 'components/ILIAS/UI/src/templates/default/Input/tpl.standard.html',
                'component' => '',
                'expected' => $il_root . '/components/ILIAS/UI/src/templates/default/Input/tpl.standard.html',
            ],
            'ui template from skin' => [
                'skin' => 'mySkin', 'style' => 'myStyle', 'file_exists' => true,
                'tpl_filename' => 'components/ILIAS/UI/src/templates/default/Input/tpl.standard.html',
                'component' => '',
                'expected' => $il_root . '/public/Customizing/skin/mySkin/myStyle/UI/Input/tpl.standard.html',
            ],
            'ui template from skin, unaltered' => [
                'skin' => 'mySkin', 'style' => 'myStyle', 'file_exists' => false,
                'tpl_filename' => 'components/ILIAS/UI/src/templates/default/Input/tpl.standard.html',
                'component' => '',
                'expected' => $il_root . '/components/ILIAS/UI/src/templates/default/Input/tpl.standard.html',
            ],
            'trailing slash' => [
                'skin' => 'default', 'style' => 'delos', 'file_exists' => true,
                'tpl_filename' => 'tpl.test.html',
                'component' => 'components/ILIAS/Test/',
                'expected' => $il_root . '/components/ILIAS/Test/templates/default/tpl.test.html',
            ],

        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('templatePathDataProvider')]
    public function testGetTemplatePath(
        string $skin,
        string $style,
        bool $file_exists,
        string $tpl_filename,
        string $component,
        string $expected
    ): void {
        $path = $this->il_template
            ->withSkin($skin)
            ->withStyle($style)
            ->withFileExists($file_exists)
            ->_getTemplatePath($tpl_filename, $component);

        $this->assertEquals($expected, $path);
    }

}
