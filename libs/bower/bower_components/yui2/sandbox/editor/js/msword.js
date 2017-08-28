//Remove the ms o: tags
html = html.replace(/<o:p>\s*<\/o:p>/g, '');
html = html.replace(/<o:p>[\s\S]*?<\/o:p>/g, '&nbsp;');

//Remove the ms w: tags
html = html.replace( /<w:[^>]*>[\s\S]*?<\/w:[^>]*>/gi, '');

//Remove mso-? styles.
html = html.replace( /\s*mso-[^:]+:[^;"]+;?/gi, '');

//Remove more bogus MS styles.
html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*;/gi, '');
html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*"/gi, "\"");
html = html.replace( /\s*TEXT-INDENT: 0cm\s*;/gi, '');
html = html.replace( /\s*TEXT-INDENT: 0cm\s*"/gi, "\"");
html = html.replace( /\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"");
html = html.replace( /\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"" );
html = html.replace( /\s*tab-stops:[^;"]*;?/gi, '');
html = html.replace( /\s*tab-stops:[^"]*/gi, '');

//Remove XML declarations
html = html.replace(/<\\?\?xml[^>]*>/gi, '');

//Remove lang 
html = html.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");

//Remove language tags
html = html.replace( /<(\w[^>]*) language=([^ |>]*)([^>]*)/gi, "<$1$3");

//Remove onmouseover and onmouseout events (from MS Word comments effect)
html = html.replace( /<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/gi, "<$1$3");
html = html.replace( /<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/gi, "<$1$3");

