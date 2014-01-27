[Less.php](http://lessphp.gpeasy.com)
========

This is a PHP port of the official LESS processor <http://lesscss.org>. [![Build Status](https://travis-ci.org/oyejorge/less.php.png?branch=master)](https://travis-ci.org/oyejorge/less.php)

* [About](#about)
* [Installation](#installation)
* [Basic Use](#basic-use)
* [Caching](#caching)
* [Source Maps](#source-maps)
* [Command Line](#command-line)
* [Integration with other projects](#integration-with-other-projects)
* [Credits](#credits)



About
---
The code structure of less.php mirrors that of the official processor which helps us ensure compatibility and allows for easy maintenance.

Please note, there are a few unsupported LESS features:

- Evaluation of JavaScript expressions within back-ticks (for obvious reasons).
- Definition of custom functions.


Installation
---

You can install the library by composer or manually.

##### Composer

Step 1. Edit your `composer.json`:

```json
{
    "require": {
        "oyejorge/less.php": "~1.5"
    }
}
```

Step 2. Install it:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```


##### Manually

Step 1. [Download the latest release](https://github.com/oyejorge/less.php/releases) and upload the php files to your server.

Step 2. Include the library through autoloader:

```php
require_once '[path to less.php]/lib/Less/Autoloader.php';
Less_Autoloader::register();
```


### Autoloading
Use the Autoloader if you're not using composer or one of the [prepackaged releases](https://github.com/oyejorge/less.php/releases).

```php
require_once 'lib/Less/Autoloader.php';
Less_Autoloader::register();
```

Basic Use
---

### Parsing Strings

```php
$parser = new Less_Parser();
$parser->parse( '@color: #4D926F; #header { color: @color; } h2 { color: @color; }' );
$css = $parser->getCss();
```


### Parsing Less Files
The parseFile() function takes two arguments:

1. The absolute path of the .less file to be parsed
2. The url root to prepend to any relative image or @import urls in the .less file.

```php
$parser = new Less_Parser();
$parser->parseFile( '/var/www/mysite/bootstrap.less', 'http://example.com/mysite/' );
$css = $parser->getCss();
```


### Handling Invalid Less
An exception will be thrown if the compiler encounters invalid less

```php
try{
	$parser = new Less_Parser();
	$parser->parseFile( '/var/www/mysite/bootstrap.less', 'http://example.com/mysite/' );
	$css = $parser->getCss();
}catch(Exception $e){
	$error_message = $e->getMessage();
}
```


### Parsing Multiple Sources
less.php can parse multiple sources to generate a single css file

```php
$parser = new Less_Parser();
$parser->parseFile( '/var/www/mysite/bootstrap.less', '/mysite/' );
$parser->parse( '@color: #4D926F; #header { color: @color; } h2 { color: @color; }' );
$css = $parser->getCss();
```

### Getting Info About The Parsed Files
less.php can tell you which .less files were imported and parsed.

```php
$parser = new Less_Parser();
$parser->parseFile( '/var/www/mysite/bootstrap.less', '/mysite/' );
$css = $parser->getCss();
$imported_files = $parser->allParsedFiles();
```


### Compressing Output
You can tell less.php to remove comments and whitespace to generate minimized css files.

```php
$options = array( 'compress'=>true );
$parser = new Less_Parser( $options );
$parser->parseFile( '/var/www/mysite/bootstrap.less', '/mysite/' );
$css = $parser->getCss();
```


### Import Directories
By default, less.php will look for @imports in the directory of the file passed to parsefile().
If you're using parse() or if @imports reside in different directories, you can tell less.php where to look.

```php
$directories = array( '/var/www/mysite/bootstrap/' => '/mysite/bootstrap/' );
$parser = new Less_Parser();
$parser->SetImportDirs( $directories );
$parser->parseFile( '/var/www/mysite/theme.less', '/mysite/' );
$css = $parser->getCss();
```


Caching
---
Compiling less code into css is a time consuming process, caching your results is highly recommended.


### Caching CSS
Use the Less_Cache class to save and reuse the results of compiled less files.
This method will check the modified time and size of each less file (including imported files) and regenerate a new css file when changes are found.
Note: When changes are found, this method will return a different file name for the new cached content.

```php
$to_cache = array( '/var/www/mysite/bootstrap.less' => '/mysite/' );
Less_Cache::$cache_dir = '/var/www/writable_folder';
$css_file_name = Less_Cache::Get( $to_cache );
$compiled = file_get_contents( '/var/www/writable_folder/'.$css_file_name );
```

### Parser Caching
less.php will save serialized parser data for each .less file if a writable folder is passed to the SetCacheDir() method.
Note: This feature only caches intermediate parsing results to improve the performance of repeated css generation.
Your application should cache any css generated by less.php.

```php
$parser = new Less_Parser();
$parser->SetCacheDir( '/var/www/writable_folder' );
$parser->parseFile( '/var/www/mysite/bootstrap.less', '/mysite/' );
$css = $parser->getCss();
```


Source Maps
---
Less.php supports v3 sourcemaps

### Inline
The sourcemap will be appended to the generated css file.

```php
$options = array( 'sourceMap' => true );
$parser = new Less_Parser($options);
$parser->parseFile( '/var/www/mysite/bootstrap.less', '/mysite/' );
$css = $parser->getCss();
```

### Saving to Map File

```php
$options = array(
	'sourceMap'			=> true,
	'sourceMapWriteTo'	=> '/var/www/mysite/writable_folder/filename.map',
	'sourceMapURL'		=> '/mysite/writable_folder/filename.map',
	);
$parser = new Less_Parser($options);
$parser->parseFile( '/var/www/mysite/bootstrap.less', '/mysite/' );
$css = $parser->getCss();
```


Command line
---
An additional script has been included to use the compiler from the command line.
In the simplest invocation, you specify an input file and the compiled css is written to standard out:

```
$ lessc input.less > output.css
```

By using the -w flag you can watch a specified input file and have it compile as needed to the output file:

```
$ lessc -w input.less output.css
```

Errors from watch mode are written to standard out.

For more help, run `lessc --help`


Integration with other projects
---

### Drupal 7

This library can be used as drop-in replacement of lessphp to work with [Drupal 7 less module](https://drupal.org/project/less).

How to install:

1. [Download the latest release](https://github.com/oyejorge/less.php/releases) and unpack it so that 'lessc.inc.php' is located at 'sites/all/libraries/lessphp/lessc.inc.php'.
2. Download and install [Drupal 7 less module](https://drupal.org/project/less) as usual.
3. That's it :)





Credits
---
less.php was originally ported to php by [Matt Agar](https://github.com/agar) and then updated by [Martin Jantošovič](https://github.com/Mordred).
