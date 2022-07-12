const findTagByName = require("./find-tag-by-name.js");

function findTagsByName(xml, tagName, options) {
  const tags = [];
  const debug = (options && options.debug) || false;
  let startIndex = (options && options.startIndex) || 0;
  let tag;
  while ((tag = findTagByName(xml, tagName, { debug, startIndex }))) {
    startIndex = tag.end;
    tags.push(tag);
  }
  if (debug) console.log("findTagsByName found", tags.length, "tags");
  return tags;
}

module.exports = findTagsByName;
