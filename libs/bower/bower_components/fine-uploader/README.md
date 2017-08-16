[![Fine Uploader](http://fineuploader.com/img/FineUploader_logo.png)](http://fineuploader.com)

![Bower](https://img.shields.io/bower/v/fine-uploader.svg?style=flat-square)
[![license](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

[**Documentation**](http://docs.fineuploader.com) |
[**Examples**](http://fineuploader.com/demos) |
[**Support**](http://fineuploader.com/support.html) |
[**Blog**](http://blog.fineuploader.com/) |
[**Changelog**](http://blog.fineuploader.com/category/changelog/)

---

## [Bower](http://bower.io) distribution build of [Fine Uploader](http://fineuploader.com)

### Usage

First, download Fine Uploader:

```bash
bower install fine-uploader --save
```

Then, simply reference the necessary JavaScript files on your HTML page:

```html
<script src="/bower_components/fine-uploader/dist/fineuploader.min.js"></script>
<link href="/bower_components/fine-uploader/dist/fineuploader.min.css" type="text/css">
```

__Enjoy__

----

### Updating or building manually

You normally should not have to do this, but you can _also_ build this distribution yourself by following the steps in this section.

#### Prepping (getting fine-uploader)

```bash
$ git clone --recursive https://github.com/FineUploader/bower-dist.git
```

OR, if you already cloned this repo;

```bash
$ cd fineuploader-dist
$ git pull origin
```

#### Building

In your terminal please navigate to where the project is cloned

```bash
$ ./build.sh <version> # e.g: ./build.sh 5.11.8
```

**NOTE:**

- The build will automaticaly install node dependencies if the node_modules directory does not exist.
- If for some reason you would like to reinstall the dependencies use `--reinstall-dep` to remove existing `node_modules` directory first. After that execute the following command:

	```bash
	$ ./build.sh <version> --reinstall-dep
	```

### Credits

* [Fery Wardiyanto](https://github.com/feryardiant) as original author of this distribution. [Buy him a coffee](https://gratipay.com/~feryardiant/).
* **Fine Uploader** is a code library sponsored by [Widen Enterprises, Inc.](http://www.widen.com/)
