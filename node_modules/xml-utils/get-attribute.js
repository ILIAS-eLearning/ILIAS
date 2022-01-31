function getAttribute(tag, attributeName, options) {
  const debug = (options && options.debug) || false;
  if (debug) console.log("getting " + attributeName + " in " + tag);

  const xml = typeof tag === "object" ? tag.outer : tag;

  const pattern = `${attributeName}\\="\([^"]*\)"`;
  if (debug) console.log("pattern:", pattern);

  const re = new RegExp(pattern);
  const match = re.exec(xml);
  if (debug) console.log("match:", match);
  if (match) return match[1];
}

module.exports = getAttribute;
