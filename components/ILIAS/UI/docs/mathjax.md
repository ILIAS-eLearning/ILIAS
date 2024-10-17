# Streamlined LaTeX usage ILIAS 10

See Feature Request https://docu.ilias.de/goto_docu_wiki_wpage_5614_1357.html

## Changes from ILIAS 9

MathJax 3 is included as a dependency in the [package.json](../../../package.json) if ILIAS:

````
    "mathjax": "^3.2.2"
````

In this way, it can be kept up-to-date and secure by ILIAS updates. The MathJax version used is unique and can be tested for updates.

The MathJax scripts are already compiled, but they load components. Therefore, the MathJax assets must be copied to a separate directory `public\node_modules\mathjax` during `composer install`. There are start scripts for various standard configurations, e.g. whether SVG or HTML should be generated. For the ILIAS core, `tex-chtml-full.js` is used, which corresponds to the recommended configuration in ILIAS 9.

MathJax 2 is no longer supported. This means that the JavaScript code in ILIAS can rely on MathJax 3 for subsequent rendering of dynamic content (e.g. for accordions or test questions in the page editor).

A MathJax server is no longer supported in the ILIAS core. The MathJax 2 server described in previous versions uses outdated components and is no longer recommended.

The processing of MathJax in ILIAS is moved from the MathJax component to the UI framework. The directory of the MathJax component is deleted.

## Setup and Configuration

The previous settings for MathJax as third-party software are completely removed.

An activation of MathJax is provided in the setup, so that MathJax can be omitted in platforms that do not require Latex. It is a new setting for the UI component:

```
	"ui": {
		"mathjax_enabled": true
	},
```

If MathJax is activated, a file [mathjax.js](../resources/js/MathJax/mathjax.js) is included on the ILIAS page, which configures and loads MathJax. It is copied to the directory `public\node_modules\mathjax` as an additional asset during `composer install`. It sets the safe mode and sets the CSS classes to activate or deactivate a Latex rendering in parts of the page. It also defines the delimiters that MathJax recognizes for Latex code. By adding the delimiters from ILIAS, e.g. [tex] and [/tex], ILIAS does not need to convert code for MathJax. An exception are the `<span>` elements for latex in the rich tect editor. The can't be treated by the javascript library of MathJax directly.

## Use in Components

All calls to `ilMathJax` are replaced.

Latex processing is now only activated by components of the UI framework.
The ILIAS components (e.g. the `ilPageObjectGUI`) have so far only processed legacy content with Latex , which can sometimes contain complex HTML. In order to minimise the migration effort, Latex is therefore initially supported by the legacy component.

Old:
````
$output = ilMathJax::getInstance()->insertLatexImages($output);
````

New:
````
$output = $this->ui->renderer()->render(
    $this->ui->factory()->legacy($output)->withLatexEnabled());
````

The rendering purpose is no longer set. The output is not processed but wrapped in a `<div>` which sets the CSS class to activate a Latex processing by the browser script. The script is added by UI component to the page.

## UI Components and Rendering

There are two new interfaces for Latex supporting components in the UI Framework:

* [LatexAwareComponent](../src/Component/LatexAwareComponent.php)
* [LatexAwareRenderer](../src/Implementation/Render/LatexAwareRenderer.php)

Their functions can be added using traits:

* [LatexAwareComponentTrait](../src/Implementation/Component/LatexAwareComponentTrait.php)
* [LatexAwareRendererTrait](../src/Implementation/Render/LatexAwareRendererTrait.php)

The functions of the components are limited to activating and deactivating latex processing in the component.

In the renderer, the content is wrapped in a `<div>` with the corresponding CSS class using `addLatexEnabling` and `addLatexDisabling`. The assets required for MathJax are added to the page with `registerMathJaxResources`. In the ILIAS core, this is the file `assets/js/mathjax.js` which configures and loads MathJax. 

The renderer receives the information about the MathJax activation, the CSS classes and assets with the function `withMathJaxConfig` of the trait. This is done in the [DefaultRendererFactory](../src/Implementation/Render/DefaultRendererFactory.php) for renderers that implement the LatexAwareRenderer interface. A class with a [MathJaxConfig](../src/Implementation/Render/MathJaxConfig.php) interface is transferred.

When the UI framework is initialised, a [MathJaxDefaultConfig](../src/Implementation/Render/MathJaxDefaultConfig.php) is transferred. The MathJax activation is read from the ILIAS settings.

## Rendering Policy

In addition to the legacy component, the [LatexAwareRenderer](../UI/src/Implementation/Render/LatexAwareRenderer.php) interface is also implemented by the standard renderer of the ILIAS page. It sets a global CSS class in the `<body>` of the page. This class prevents a Latex processing on the page by default and only components that have a CSS class activating it will be processed. 

This policy restricts the processing of Latex to the places intended by ILIAS (see https://docu.ilias.de/goto_docu_wiki_wpage_5614_1357.html).

## MathJax Plugin

A plugin is planned to support the ILIAS installations that would be restricted by the changes in ILIAS 10. It can independently realize the following functions:

* Enable Latex processing on the entire page so that content that uses Latex in unintended places continues to look as before.

* Change the configuration and the loaded components of MathJax so that, for example, SVG is generated instead of HTML or special assistive functions are activated.

* Call a MathJax 3 server for rendering Latex expressions. This can speed up the display of Latex in browsers and facilitate the server side generation of PDF files by other plugins (e.g. the TestArchiveCreator).

The plugin will use the methods `exchangeUIRendererAfterInitialisation` and `exchangeUIFactoryAfterInitialisation` to process the page content shortly before delivery to the browser. This way a MathJax server can work the same way as MathJax in the browser and render latex at all paces if that is set by the plugin.
