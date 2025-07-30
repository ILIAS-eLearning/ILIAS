# MathJax Service

This service is deprecated. Please replace it with the use of `ILIAS\UI\Component\Legacy\LatexContent`:

Old:

````php
$output = ilMathJax::getInstance()->insertLatexImages($input);
````

New: 

````php
$output = $this->ui->renderer()->render($this->ui->factory()->legacy()->latexContent($input));
````

See the feature request [Streamline LaTeX usage](https://docu.ilias.de/go/wiki/wpage_5614_1357) for details.
