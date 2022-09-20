# MathJax Service

This service renders TeX expressions found at different places in ILIAS content to a display of the expressed formula. The rendering can be done either in the browser by an included JavaScript of MathJax or on the server. See [Server installation](docs/install-server.md) on how to install a mathjax server.

Both rendering types are configured in *Administration > Extending ILIAS > Third Party Software > MathJax*. The same configuration can also be done by settings in the config file of the ILIAS seup, see the [Setup README](../../setup/README.md) for details. If some settings are configured in the setup, they will overwrite the manual settings with the next update. 

Please note that TeX content is currently only supported at specific places in ILIAS, e.g. page editor content or content produced by the TinyMCE rich text editor. If TeX is rendered in the browser and the included MathJax script finds TeX expressions with the supported delimiters at other places, these will be rendered, too. However, if TeX must be rendered on the server, e.g. for PDF generation, these places will be ignored. See https://docu.ilias.de/goto_docu_wiki_wpage_5614_1357.html for details.

Server side rendered images will be cached in the ILIAS directory for web contents (path temp/tex). This cache can be deleted.

## Use by Developers

### Standard Call by Components

If a component needs to render TeX expressions in its content, it should be done like this:

    $content = ilMathJax::getInstance()->insertLatexImages($content);

The *getInstance()* will ensure that the rendering type set for the current request will be taken. The *insertLatexImages()* will search for TeX expressions within [tex] and [/tex] delimiters. If your content uses other delimiters, add them as parameters.

ilMathJax will be automatically initialized for a browser display of the content and the rendering type will be chosen according to the global configuration. This can be changed with the *init()* function.

### Use for HTML Exports

To produce an HTML export of content, the server-side rendering must create a different output by using img tags instead of direct svg. This has to be initialized as early as possible in the request, before any content is rendered with *insertLatexImages()*.

    ilMathJax::getInstance()->init(ilMathJax::PURPOSE_EXPORT);

### Use for PDF Generation 

This applies to PDF generations configured under Administration > System Settings and Maintenance > PDF generation.

If the current request will produce a PDF, the server-side rendering must create a different output by using png images instead of svg. This has to be initialized as early as possible in the request, before any content is rendered with *insertLatexImages()*.

    ilMathJax::getInstance()->init(ilMathJax::PURPOSE_PDF);

### Use for PDF generation via Java Server

This applies to PDF generations using the FO processing from the java server of ILIAS for SCORM 2004 and certificates.

If the current request will produce a PDF by FO processing, the server-side rendering must be deferred until the content is finally prepared for the FO server. At the beginning of the request, ilMathJax must be initialized to prevent an early rendering. TeX formulas will then be masked when the components call *insertLatexImages()*.

    ilMathJax::getInstance()->init(ilMathJax::PURPOSE_DEFERRED_PDF);

At the end, just before the fo string is sent to the java server, render the masked TeX code:

        $fo_string = ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
            ->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
            ->insertLatexImages($content);

Note that this only works if the java server has read access to the ilias web directory where the rendered images are cached.


## Update with ILIAS 8
The whole component was refactored for PHP 8 and current ILIAS coding standards.

* Mathjax 3 is supported in the browser with an additional polyfill url.
* The Mimetex CGI (formerly configured with path_to_latex_cgi in the setup) is not longer supported, see https://docu.ilias.de/goto.php?target=wiki_1357_Abandon_mimetex_support
* All configuration can also be done in the setup.
* The configuration uses an immutable object and repository.
* The image cache uses the filesystem service interface.
* The settings GUI is moved from ilObjExternalToolsSettingsGUI to an own class in the service and uses the ui framework for the settings form.
* The deprecated ilUtil functions *includeMathJax*, *insertLatexImages* and *buildLatexImages* are removed. They were not used in the core anymore.
* The obsolete rendering modes RENDER_SVG_AS_IMG_FILE and RENDER_PNG_AS_IMG_FILE are removed. They were not used in the core anymore.
* An initial unit test suite is added.