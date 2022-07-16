<?php

/**
 * Responsible for loading the UI Framework into the dependency injection container of ILIAS
 */
class InitUIFramework
{
    public function init(\ILIAS\DI\Container $c) : void
    {
        $c["ui.factory"] = function ($c) {
            $c["lng"]->loadLanguageModule("ui");
            return new ILIAS\UI\Implementation\Factory(
                $c["ui.factory.counter"],
                $c["ui.factory.button"],
                $c["ui.factory.listing"],
                $c["ui.factory.image"],
                $c["ui.factory.panel"],
                $c["ui.factory.modal"],
                $c["ui.factory.dropzone"],
                $c["ui.factory.popover"],
                $c["ui.factory.divider"],
                $c["ui.factory.link"],
                $c["ui.factory.dropdown"],
                $c["ui.factory.item"],
                $c["ui.factory.viewcontrol"],
                $c["ui.factory.chart"],
                $c["ui.factory.input"],
                $c["ui.factory.table"],
                $c["ui.factory.messagebox"],
                $c["ui.factory.card"],
                $c["ui.factory.layout"],
                $c["ui.factory.maincontrols"],
                $c["ui.factory.tree"],
                $c["ui.factory.menu"],
                $c["ui.factory.symbol"],
                $c["ui.factory.toast"],
                $c["ui.factory.legacy"]
            );
        };
        $c["ui.upload_limit_resolver"] = function ($c) {
            return new \ILIAS\UI\Implementation\Component\Input\UploadLimitResolver(
                (int) \ilFileUtils::getUploadSizeLimitBytes()
            );
        };
        $c["ui.signal_generator"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\SignalGenerator();
        };
        $c["ui.factory.counter"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Counter\Factory();
        };
        $c["ui.factory.button"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Button\Factory();
        };
        $c["ui.factory.listing"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Listing\Factory();
        };
        $c["ui.factory.image"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Image\Factory();
        };
        $c["ui.factory.panel"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Panel\Factory($c["ui.factory.panel.listing"]);
        };
        $c["ui.factory.modal"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Modal\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.dropzone"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Dropzone\Factory($c["ui.factory.dropzone.file"]);
        };
        $c["ui.factory.popover"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Popover\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.divider"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Divider\Factory();
        };
        $c["ui.factory.link"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Link\Factory();
        };
        $c["ui.factory.dropdown"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Dropdown\Factory();
        };
        $c["ui.factory.item"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Item\Factory();
        };
        $c["ui.factory.toast"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Toast\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.viewcontrol"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\ViewControl\Factory(
                $c["ui.signal_generator"]
            );
        };
        $c["ui.factory.chart"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Chart\Factory(
                $c["ui.factory.progressmeter"],
                $c["ui.factory.bar"]
            );
        };
        $c["ui.factory.input"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Factory(
                $c["ui.signal_generator"],
                $c["ui.factory.input.field"],
                $c["ui.factory.input.container"],
                $c["ui.factory.input.viewcontrol"]
            );
        };
        $c["ui.factory.table"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Table\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.messagebox"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\MessageBox\Factory();
        };
        $c["ui.factory.card"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Card\Factory();
        };
        $c["ui.factory.layout"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Layout\Factory();
        };
        $c["ui.factory.maincontrols.slate"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\MainControls\Slate\Factory(
                $c['ui.signal_generator'],
                $c['ui.factory.counter'],
                $c["ui.factory.symbol"]
            );
        };
        $c["ui.factory.maincontrols"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\MainControls\Factory(
                $c['ui.signal_generator'],
                $c['ui.factory.maincontrols.slate']
            );
        };
        $c["ui.factory.menu"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Menu\Factory(
                $c['ui.signal_generator']
            );
        };
        $c["ui.factory.symbol.glyph"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory();
        };
        $c["ui.factory.symbol.icon"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Icon\Factory();
        };
        $c["ui.factory.symbol.avatar"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Avatar\Factory();
        };
        $c["ui.factory.symbol"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Factory(
                $c["ui.factory.symbol.icon"],
                $c["ui.factory.symbol.glyph"],
                $c["ui.factory.symbol.avatar"]
            );
        };
        $c["ui.factory.progressmeter"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Chart\ProgressMeter\Factory();
        };
        $c["ui.factory.bar"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Chart\Bar\Factory();
        };
        $c["ui.factory.input.field"] = function ($c) {
            $data_factory = new ILIAS\Data\Factory();
            $refinery = new ILIAS\Refinery\Factory($data_factory, $c["lng"]);

            return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
                $c["ui.upload_limit_resolver"],
                $c["ui.signal_generator"],
                $data_factory,
                $refinery,
                $c["lng"]
            );
        };
        $c["ui.factory.input.container"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Container\Factory(
                $c["ui.factory.input.container.form"],
                $c["ui.factory.input.container.filter"],
                $c["ui.factory.input.container.viewcontrol"]
            );
        };
        $c["ui.factory.input.container.form"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Container\Form\Factory(
                $c["ui.factory.input.field"]
            );
        };
        $c["ui.factory.input.container.filter"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(
                $c["ui.signal_generator"],
                $c["ui.factory.input.field"]
            );
        };
        $c["ui.factory.input.container.viewcontrol"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Container\ViewControl\Factory();
        };
        $c["ui.factory.input.viewcontrol"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\ViewControl\Factory();
        };
        $c["ui.factory.dropzone.file"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Dropzone\File\Factory(
                $c["ui.upload_limit_resolver"],
                $c["ui.factory.input"],
                $c["lng"]
            );
        };
        $c["ui.factory.panel.listing"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Panel\Listing\Factory();
        };
        $c["ui.renderer"] = function ($c) {
            return new ILIAS\UI\Implementation\DefaultRenderer(
                $c["ui.component_renderer_loader"]
            );
        };
        $c["ui.component_renderer_loader"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\LoaderCachingWrapper(
                new ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper(
                    $c["ui.resource_registry"],
                    new ILIAS\UI\Implementation\Render\FSLoader(
                        new ILIAS\UI\Implementation\Render\DefaultRendererFactory(
                            $c["ui.factory"],
                            $c["ui.template_factory"],
                            $c["lng"],
                            $c["ui.javascript_binding"],
                            $c["refinery"],
                            $c["ui.pathresolver"]
                        ),
                        new ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory(
                            $c["ui.factory"],
                            $c["ui.template_factory"],
                            $c["lng"],
                            $c["ui.javascript_binding"],
                            $c["refinery"],
                            $c["ui.pathresolver"]
                        ),
                        new ILIAS\UI\Implementation\Component\Symbol\Icon\IconRendererFactory(
                            $c["ui.factory"],
                            $c["ui.template_factory"],
                            $c["lng"],
                            $c["ui.javascript_binding"],
                            $c["refinery"],
                            $c["ui.pathresolver"]
                        ),
                        new ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory(
                            $c["ui.factory"],
                            $c["ui.template_factory"],
                            $c["lng"],
                            $c["ui.javascript_binding"],
                            $c["refinery"],
                            $c["ui.pathresolver"]
                        )
                    )
                )
            );
        };
        $c["ui.template_factory"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\ilTemplateWrapperFactory($c["tpl"]);
        };
        $c["ui.resource_registry"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\ilResourceRegistry($c["tpl"]);
        };
        $c["ui.javascript_binding"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\ilJavaScriptBinding($c["tpl"]);
        };

        $c["ui.factory.tree"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Tree\Factory();
        };

        $c["ui.factory.legacy"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Legacy\Factory($c["ui.signal_generator"]);
        };

        $c["ui.pathresolver"] = function ($c) : ILIAS\UI\Implementation\Render\ImagePathResolver {
            return new ilImagePathResolver();
        };
    }
}
