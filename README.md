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
  require_once 'autoload.php';                           // Comment this line if you use Composer to install the package
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
  require_once 'autoload.php';                           // Comment this line if you use Composer to install the package
  use \Convertio\Convertio;

  $API = new Convertio("_YOUR_API_KEY_");           // You can obtain API Key here: https://convertio.co/api/
  $API->start('./input.docx', 'pdf')->wait()->download('./output.pdf')->delete();
```

Following example will extract clean text from DOCX:
```php
<?php
  require_once 'autoload.php';                           // Comment this line if you use Composer to install the package
  use \Convertio\Convertio;

  $API = new Convertio("_YOUR_API_KEY_");           // You can obtain API Key here: https://convertio.co/api/
  $Text = $API->start('./test.docx', 'txt')->wait()->fetchResultContent()->result_content;
  $API->delete();
  echo $Text;
```

Following example will override default API parameters in case you don't have SSL enabled in your PHP installation or want to limit execution time:
```php
<?php
  require_once 'autoload.php';                           // Comment this line if you use Composer to install the package
  use \Convertio\Convertio;

  $API = new Convertio("_YOUR_API_KEY_");           // You can obtain API Key here: https://convertio.co/api/
  $API->settings(array('api_protocol' => 'http', 'http_timeout' => 10));
  $API->startFromURL('http://google.com/', 'png')->wait()->download('./google.png')->delete();
```

Installation
-------------------
You can use **Composer** or simply **Download the Release**

#### Composer
The preferred method is via [composer](https://getcomposer.org). Follow the
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require convertio/convertio-php
```

Finally, be sure to include the autoloader:

```php
<?php
require_once '/path/to/your-project/vendor/autoload.php';
```

#### Download the Release
You can download the package in its entirety. The [Releases](https://github.com/convertio/convertio-php/releases) page lists all stable versions.
Download any file with the name `convertio-php-[RELEASE_NAME].zip` for a package including this library and its dependencies.
Uncompress the zip file you download, and include the autoloader in your project:

```php
<?php
  require_once '/path/to/convertio-php/autoload.php';
  use \Convertio\Convertio;
  $API = new Convertio("_YOUR_API_KEY_");
  //...
```

Example with exceptions catching
-------------------
The following example shows how to catch the different exception types which can occur at conversions:

```php
<?php
  require_once 'autoload.php';                           // Comment this line if you use Composer to install the package

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
