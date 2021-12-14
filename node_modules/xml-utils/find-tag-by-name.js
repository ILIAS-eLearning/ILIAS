const indexOfMatch = require("./index-of-match.js");

function findTagByName(xml, tagName, options) {
  const debug = (options && options.debug) || false;

  const startIndex = (options && options.startIndex) || 0;

  if (debug) console.log("starting findTagByName with", tagName, " and ", options);

  const start = indexOfMatch(xml, `\<${tagName}[ \>]`, startIndex);
  if (debug) console.log("start:", start);
  if (start === -1) return undefined;

  const afterStart = xml.slice(start + tagName.length);
  let relativeEnd = indexOfMatch(afterStart, "[ /]" + tagName + ">", 0);
  const selfClosing = relativeEnd === -1;

  if (selfClosing) {
    relativeEnd = indexOfMatch(afterStart, "[ /]>", 0);
  }

  const end = start + tagName.length + relativeEnd + 1 + (selfClosing ? 0 : tagName.length) + 1;
  if (debug) console.log("end:", end);
  if (end === -1) return undefined;

  const outer = xml.slice(start, end);
  // tag is like <gml:identifier codeSpace="OGP">urn:ogc:def:crs:EPSG::32617</gml:identifier>

  let inner;
  if (selfClosing) {
    inner = null;
  } else {
    inner = outer.slice(outer.indexOf(">") + 1, outer.lastIndexOf("<"));
  }

  return { inner, outer, start, end };
}

module.exports = findTagByName;
