# Media Objects

## Access Handling

- Media objects can be re-used, e.g. when pages are copied or media objects are inserted from a media pool in a learning module.
- All references "are equal". The container handles write access to the media object. This mostly means that write access to a container like a learning module gives write access to the media object.

## Media Types

- ILIAS fully supports image types jpg, gif, svg, png, mp3 and mp4 files.
- ILIAS 8 supports external youtube and vimeo references. Since it uses mediaelement.js for rendering, not all features of youtube and vimeo are supported. On the other hand mediaelement.js is not able to fully deactivate/hide all features of the native youtube or vimeo presentation.
- Please note that uploaded svg files will be processed by a sanitizer to eliminate potential insecure parts. This might even lead to non-working svg files, if they rely on these features.
- PDF rendering support is limited. You might get different results, depending on server configuration and browser version. ILIAS renders the PDF as iframe with a src attribute pointing to the PDF file. The server must be configured to sent PDF files as application/pdf. The browser has to include a builtin PDF viewer. You should at least specify a height either directly or through content CSS rules.
- HTML media objects need to be activated in the admininistration under "Repository and Objects > Allowed File Types". Please note that this is a potential security risk since this allows to upload HTML/Javascript, e.g. in page editor content. This content is rendered in iframes and since all media objects are located in a special folder, you might try to configure your webserver in a sub-domain isolation manner (currently untested). Since iframes are used you should at least specify a height either directly or through content CSS rules.
- Using the content style editor allows to define heights especially for PDF/HTML objects (keep the width empty to get a 100% width default behaviour.) e.g.
  - in px
  - relative to the viewport (e.g. setting "height: 80vh" as custom parameter)
  - with an aspect ratio (e.g. setting "aspect-ratio: 16/9" as custom parameter)
- SVGs files are rendered as embed-tags inside content pages. ILIAS tries to render SVG files "image like", but please note that SVG allows to define a much more complex behaviour. You should declare a viewBox in your SVG to give it aspect ratio information. Specifying additional width and height in the SVG will currently give it a default size in most browsers. When using the page editor you should either define a size in the properties of the media object or define at least the width per css in an attached content style class, e.g. 100% if you want it to scale with its container. 

## Video

- ILIAS renders the video tag with the attribute `preload="auto"` to tell the browser to load the basic video data like the duration into the player. In almost all browsers this enables a preview image of the video, but not in all, e.g. Safari decides to save bandwidth instead.

## Constraint Proportions

This checkbox in the advanced editing form synchronises the width and height input field while editing to keep the original proportion. It only works on images with a given width/height. This is not a property being saved, it only acts during modification of the input fields.