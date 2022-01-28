# xml-utils
> The lightest XML parsing library

## features
- Only import the functions that you need
- No external dependencies
- Runs the same in NodeJS and Browser

## install
```bash
npm install xml-utils
```

# usage

## the simple tag object
XML tags are represented by a simple object with an outer and inner property. 
The "outer" property is the text string that completely encompasses the tag, equivalent to an HTML element's "outerHTML".
The "inner" property represents the sub-parts of the tag.  It is similar to an HTML element's "textContent".
Here's an example of a tag:
```javascript
{
  outer: "<MDI key="INTERLEAVE">PIXEL</MDI>",
  inner: "PIXEL"
}
```

## get attribute
```javascript
const getAttribute = require("xml-utils/get-attribute");
const xml = `<MDI key="INTERLEAVE">PIXEL</MDI>`;
const key = getAttribute(xml, "key");
// key is "INTERLEAVE"
```

## find one tag by name
```javascript
const findTagByName = require("xml-utils/find-tag-by-name");

const xml = `<Metadata domain="IMAGE_STRUCTURE"><MDI key="INTERLEAVE">PIXEL</MDI></Metadata>`
const tag = findTagByName(xml, "MDI");
```
tag is
```javascript
{
  outer: "<MDI key="INTERLEAVE">PIXEL</MDI>",
  inner: "PIXEL"
}
```
## find multiple tags with the same name
```javascript
const findTagsByName = require("xml-utils/find-tags-by-name");
const xml = `
    <Metadata>
      <MDI key="SourceBandIndex">1</MDI>
      <MDI key="STATISTICS_MAXIMUM">255</MDI>
      <MDI key="STATISTICS_MEAN">96.372431147996</MDI>
      <MDI key="STATISTICS_MINIMUM">0</MDI>
      <MDI key="STATISTICS_STDDEV">50.057898474622</MDI>
    </Metadata>
`;
const tags = findTagsByName(xml, "MDI");
// tags is an array of tags
```
## find one tag by path
```javascript
const findTagByPath = require("xml-utils/find-tag-by-path");
const xml = `
       <gmd:referenceSystemIdentifier>
         <gmd:RS_Identifier>
           <gmd:code>
             <gco:CharacterString>4326</gco:CharacterString>
           </gmd:code>
           <gmd:codeSpace>
             <gco:CharacterString>EPSG</gco:CharacterString>
           </gmd:codeSpace>
           <gmd:version>
             <gco:CharacterString>6.11</gco:CharacterString>
           </gmd:version>
         </gmd:RS_Identifier>
       </gmd:referenceSystemIdentifier>
       `;
const tag = findTagByPath(xml, ["gmd:RS_Identifier", "gmd:code", "gco:CharacterString"]);
```

## find multiple tags by path
To get an array of tags that follow a path:
```javascript
const findTagsByPath = require("xml-utils/find-tags-by-path");
const tags = findTagByPath(xml, ["Metadata", "MDI"]);
// tags is an array of tags
```


## setup
download test files with:
```bash
npm run setup
```

## test
```bash
npm run test
```

# contact
Post an issue at https://github.com/DanielJDufour/xml-utils/issues or email the package author at daniel.j.dufour@gmail.com
