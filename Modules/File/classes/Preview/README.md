# File-Preview in ILIAS >= 9
ILIAS supports the preview for different file-types. To create preview, the following php-libraies are needed:

- php-imagick
- php-gd

To create previews of PDFs, your server needs some further configuration. php-imagick can only create previews of PDFs, if the ghostscript is installed on your server and configured to be used by php-imagick. By default, in /etc/ImageMagick-6/policy.xml the ghostscript formats are disabled. you can activate them by removing the lines from the policy.xml:

```bash
sed -i '/disable ghostscript format types/,+6d' /etc/ImageMagick-6/policy.xml 
```
