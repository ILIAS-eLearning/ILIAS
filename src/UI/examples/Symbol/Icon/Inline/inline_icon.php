<?php
function inline_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $raw_icon_data = '<?xml version="1.0" encoding="utf-8"?>
<!-- Generator: Adobe Illustrator 23.0.3, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<svg version="1.1" id="Icons" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 32 32" style="enable-background:new 0 0 32 32;" xml:space="preserve">
<style type="text/css">
	.st0{fill:none;stroke:#000000;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}
</style>
<rect x="6" y="15" class="st0" width="20" height="13"/>
<rect x="5" y="10" class="st0" width="22" height="5"/>
<rect x="12" y="10" class="st0" width="8" height="5"/>
<rect x="13" y="15" class="st0" width="6" height="13"/>
<path class="st0" d="M23.7,5.4c-1-2-3.8-1.9-5,0.2L16,10l4.8,0C23.1,9.9,24.7,7.4,23.7,5.4z"/>
<path class="st0" d="M8.3,5.4c1-2,3.8-1.9,5,0.2L16,10l-4.8,0C8.9,9.9,7.3,7.4,8.3,5.4z"/>
</svg>
';

    $base_64_encode = base64_encode($raw_icon_data);
    $ico = $f->symbol()->icon()->inline($base_64_encode, 'image/svg+xml', 'Example');

    return $renderer->render($ico);
}
