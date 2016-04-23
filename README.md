Convertio APIs Client Library for PHP
=======================

This is a lightweight wrapper for the [Convertio](https://convertio.co/api/) API.

Feel free to use, improve or modify this wrapper! If you have questions contact us or open an issue on GitHub.

Requirements
-------------------
* [PHP 5.3.0 or higher with CURL Support](http://www.php.net/)

Developer Documentation
-------------------
You can find full API reference here: https://convertio.co/api/docs/

Quickstart
-------------------
Following example will render remote web page into PNG image:
```php
<?php
  require 'autoload.php';
  use \Convertio\Convertio;

  $API = new Convertio("_YOUR_API_KEY_");           // You can obtain API Key here: https://convertio.co/api/

  $API->startFromURL('http://google.com/', 'png')   // Convert (Render) HTML Page to PNG
  ->wait()                                          // Wait for conversion finish
  ->download('./google.png')                        // Download Result To Local File
  ->delete();                                       // Delete Files from Convertio hosts
```

Following example will convert local DOCX file to PDF:
```php
<?php
  require 'autoload.php';
  use \Convertio\Convertio;

  $API = new Convertio("_YOUR_API_KEY_");           // You can obtain API Key here: https://convertio.co/api/
  $API->start('./input.docx', 'pdf')->wait()->download('./output.pdf')->delete();
```

Installation
-------------------
You can download the package in its entirety. The [Releases](https://github.com/convertio/convertio-php/releases) page lists all stable versions.
Download any file with the name `convertio-php-[RELEASE_NAME].zip` for a package including this library and its dependencies.
Uncompress the zip file you download, and include the autoloader in your project:

```php
<?php
  require '/path/to/convertio-php/autoload.php';
  use \Convertio\Convertio;
  $API = new Convertio("_YOUR_API_KEY_");
  //...
```

Example with exceptions catching
-------------------
The following example shows how to catch the different exception types which can occur at conversions:

```php
<?php
  require_once "autoload.php";

  use \Convertio\Convertio;
  use \Convertio\Exceptions\APIException;
  use \Convertio\Exceptions\CURLException;

  try {
      $API = new Convertio("_YOUR_API_KEY_");
      $API->start('./test.pdf', 'docx')->wait()->download('test.docx')->delete();
  } catch (APIException $e) {
      echo "API Exception: " . $e->getMessage() . " [Code: ".$e->getCode()."]" . "\n";
  } catch (CURLException $e) {
      echo "HTTP Connection Exception: " . $e->getMessage() . " [CURL Code: ".$e->getCode()."]" . "\n";
  } catch (Exception $e) {
      echo "Miscellaneous Exception occurred: " . $e->getMessage() . "\n";
  }
```

Resources
---------

* [API Documentation](https://convertio.co/api/docs/)
* [Conversion Types](https://convertio.co/formats)
