To create and publish a release, perform the following steps:

### Create a release branch

In order to make these release-related changes, create a branch in your repository clone.
Note that all the examples below use 2.11.0 as the release version, you'll want to use the appropriate version numbers for the release you're working toward.

    git checkout -b v2.11.0 origin/main

### Bump the version in package.json

We use [semantic versioning](https://semver.org). Set the correct `"version"` in package.json. Run `npm install` so `package-lock.json` can pick up the changes.

Edit `CHANGELOG.md`: Add the version you are about to release just below the `## Next version` heading. Review the changes since the last release and document changes as appropriate.

Commit the changes.

    git add package.json package-lock.json CHANGELOG.md
    git commit -m "Set version to 2.11.0"

### Update README when API docs changed

To build the docs, run

    npm run doc

When the above results in changes to README.md, commit these changes to master:

    git add README.md
    git commit -m "Update API docs in README"

### Merge the release branch

Create a pull request and merge the release branch. This allows for any final review of upgrade notes or other parts of the changelog.

### Publish to npm

    npm publish

### Commit release artifacts

    git add -f dist
    git commit -m "Add dist for v2.11.0"

### Create and push a tag

    git tag -a v2.11.0 -m "2.11.0"
    git push --tags origin

### Edit the release notes

The previous step creates a release on GitHub. Copy the changelog for the relese from `CHANGELOG.md` to the "Describe this release" field for the release notes on https://github.com/openlayers/ol-mapbox-style/releases.
