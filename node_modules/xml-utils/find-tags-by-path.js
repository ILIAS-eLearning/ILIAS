const findTagsByName = require("./find-tags-by-name.js");

function findTagsByPath(xml, path, options) {
  const debug = (options && options.debug) || false;
  const returnOnFirst = (options && options.returnOnFirst) || false;
  let tags = findTagsByName(xml, path.shift(), { debug });
  if (debug) console.log("first tags are:", tags);
  for (let pathIndex = 0; pathIndex < path.length; pathIndex++) {
    const tagName = path[pathIndex];
    if (debug) console.log("tagName:", tagName);
    let allSubTags = [];
    for (let tagIndex = 0; tagIndex < tags.length; tagIndex++) {
      const tag = tags[tagIndex];
      const subTags = findTagsByName(tag.outer, tagName, {
        debug,
        startIndex: 1
      });
      if (debug) console.log("subTags.length:", subTags.length);
      if (subTags.length > 0) {
        subTags.forEach(subTag => {
          (subTag.start += tag.start), (subTag.end += tag.start);
        });
        if (returnOnFirst && pathIndex === path.length - 1) return [subTags[0]];
        allSubTags = allSubTags.concat(subTags);
      }
    }
    tags = allSubTags;
  }
  return tags;
}

module.exports = findTagsByPath;
