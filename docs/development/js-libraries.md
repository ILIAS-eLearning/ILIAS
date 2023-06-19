# JavaScript Libraries

ILIAS uses `npm` to manage client-side third-party libraries, which is the popular choice for many applications.

The update-management of third-party libraries has become a complex task over the past, considering the large amount of
dependencies. We would therefore like to keep the list of required dependencies as small as possible, so we don't blow
up this task much further.

## Introducing Libraries

To introduce a new third-party library you must follow these steps:

#### 1. Fact-check

Before proposing a new library, please check if standard HTML/CSS does not fit your needs (e.g. effecs or animations).

If the third-party library is still necessary, please try to avoid using libraries with dependencies to other libraries.
Also try to avoid libraries which still depend on jQuery, because as documented in
our [JavaScript code-style](js-coding-style.md) it should not be used whenever possible.

Otherwise, please check the following facts before proposing the new library:

- is the library still actively maintained?
- is the library supported by all relevant browsers?
- is the library license compatible with ILIAS (GPL-3.0)?

#### 2. Proposal

If the library meets our criterea, it must be accepted by
the [Jour Fixe](https://docu.ilias.de/goto_docu_wiki_wpage_1_1357.html)
or [Technical Board](https://docu.ilias.de/goto_docu_cat_12438.html) of the ILIAS society first.

To propose a library you must create an according pull-request to
our [GitHub repository](https://github.com/ILIAS-eLearning/ILIAS) and add the "jour-fixe" label to it.

For ILIAS 9 and above, you must only commit changes to the `package.json` and `package-lock.json` files the
pull-request. For ILIAS 8 and below the `node_modules` folder is still a part of the repository, so changes in
these versions may also be made to this directory. The changes should be accomplished by installing the library
with `npm`:

```bash
# install npm library for production:
npm install "package-name"
```

```bash
# install npm library for development:
npm install --save-dev "package-name"
```

```bash
# install library from github:
npm install "account/repositoy#branch"
```

If the library is proposed for production, an additional entry in the `package.json` file must be made afterwards in
the `"extra": {...}` section, which looks as follows:

```json
"package-name": {
"introduction-date": "YYYY-mm-dd",
"approved-by": "Jour Fixe", // "Jour Fixe" or "Technical Board":
"developer": "(User)name of the developer who introduced the library.",
"purpose": "Describe the reason why this library is needed in ILIAS.",
"last-update-for-ilias": "9.0" // ILIAS version that last updated this library:
}
```

## Updating Libraries

ILIAS often uses the same libraries across releases, so when updating one or more libraries, they must also be updated
in all other branches of maintained ILIAS versions.

**Attention, we must not [`cherry-pick`](https://git-scm.com/docs/git-cherry-pick) update-commits from different
branches, because doing so leads to problems with with `package-lock.json`, artifacts and transitive dependencies.**

We therefore have to update libraries on each branch separately using `npm`:

```bash
# update npm library
npm update "package-name" --save
```

```bash
# update library installed from github
npm uninstall "package-name" && \
npm install "account/repository#branch"
```
