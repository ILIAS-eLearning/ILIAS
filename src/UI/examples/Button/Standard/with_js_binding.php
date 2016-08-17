<?php
function with_js_binding() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
		$f->button()->standard("Goto ILIAS", "http://www.ilias.de")
			->withOnLoadCode(function($id) {
				return 
					"$(\"#$id\").attr(\"href\", \"#\");\n".
					"$(\"#$id\").click(function() { alert(\"Clicked: $id\"); });";
			})
	);
}
