# Cross-Platform `npm` Usage

## Background

[An issue was discovered](https://github.com/ILIAS-eLearning/ILIAS/pull/9361) (and fixed) where the `package-lock.json`
did not include all required optional transitive dependencies, which caused `rollup` to be broken on some platforms
after installation.

## Objective

This investigation aimed to understand the behaviour of `npm` regarding optional dependencies, to assess the impact on
deterministic installations across different operating systems and CPU architectures.

## Key Findings

**Platform-dependant package installation:**

- `npm` will only ever install (download into `node_modules`) an optional dependency, if the 
  [`os`](https://docs.npmjs.com/cli/v11/configuring-npm/package-json#os) and
  [`cpu`](https://docs.npmjs.com/cli/v11/configuring-npm/package-json#cpu) values match the given environment.
- The environment is configurable using an [`.npmrc`](https://docs.npmjs.com/cli/v11/configuring-npm/npmrc) file.
- The `--omit=optional` flag skips installing optional dependencies, but **does not** affect the `package-lock.json`
  file.

**Lock-file behaviour:**

- The `package-lock.json` **always contains the full dependency graph**, including all of the optional (and transitive)
  dependencies.
- Optional dependencies cannot be removed from the `package-lock.json`, only pruned from the `node_modules` directory.
- `npm` uses a hidden `node_modules/.package-lock.json` file, which may prevent proper updates inside this directory,
  unless it is removed.

**Deterministic installations:**

- `npm clean-install` is less deterministic than expected, since installations vary based on the `os` and `cpu` values
  of the environment.
- Installations remain reproducible nonetheless, because the full dependency graph (should) always be contained inside
  the `package-lock.json`.
- `npm` does not provide a mechanism in order to list/show the actually installed packages inside `node_modules`. This
  needs to be done manually.

## Implications

The discovered issue was most likely caused by [a known `npm` bug](https://github.com/npm/cli/issues/7961), where
optional dependencies were inconsistently included inside the `package-lock.json`. The issue was not reproducible, even
using older versions, therefore an assumption was made. However, this assumption gets backed by the fact we could not
manage to get rid of optional dependencies inside the lock-file.

The annual merge of dependency PR's should still be performed with caution. We must ensure to use the latest version of
`npm` to prevent issues like this in the future. This can be achieved using the
[`engines`](https://docs.npmjs.com/cli/v11/configuring-npm/package-json#engines) and
[`devEngines`](https://docs.npmjs.com/cli/v11/configuring-npm/package-json#devengines) fields in our `package.json`,
which abort the process if some min-requirement is not met.

In addition, its recommended to install all of the dependencies individually using `npm install` and delete the
`node_modules` directory and `package-lock.json` file beforehand.

## Next steps

- These findings should be properly documented inside
  [`02-dependencies` chapter](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/devguide/tutorial/02-tools/02-dependencies.md)
  of the future ILIAS Dev-Guide.
- The annual merge of dependency PR's should be standardised, so that all service providers which could be responsible
  are using the same procedure / strategy.
- The `package.json` of the project should be extended by a `engines` and `devEngines` field, to ensure the `node` and
  `npm` version in use.
- The JF should be informed about the findings of this investigation.
