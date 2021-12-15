# MegaOptim
MegaOptim provides REST based APIs for optimizing images while keeping them almost identical to the original image but significantly reducing the image size using its advanced algorithms.

The end result is to satisfy the pagespeed requirements, fast website load, less space usage, etc.

This library can be installed with composer or without composer and can be used in differet use cases such as optimizing your library, optimizing the uploaded image on the go, etc.

# Requirements
- Api Key from MegaOptim.com

# Installation
### Composer
You can install it with composer
```
composer require megaoptim/megaoptim-php
```
### Without Composer
This is not recommended way. But you can require the `loadnoncomposer.php` file that will load the library without composer.
```php
require "megaoptim-php/loadnoncomposer.php"
```

# How to Use
This library has a class that will be used in 99% of the usecases and it is called ```MegaOptim\Optimizer``` and the ```run``` method is the method used for optimization. Its definition is as follows:

```
public function run($resource, $args = array())
```
1. ```$resource``` - `string|array`  The resources path, can be: Single image path OR Single URL OR multiple local paths OR multiple urls in array. **But not mixed array of urls and paths**.
2. ```$args``` - `array` The parameters that describe how the optimization is going to be. It has few properties:    
  * ```'keep_exif'``` - 1 OR 0
  * ```'max_width'``` - Numeric px value
  * ```'max_height'``` - Numeric px value
  * ```'cmyktorgb'``` - 1 OR 0
  * ```'compression'``` - intelligent (default), ultra OR lossless
  * ```'webp'``` - 1 OR 0 (generate or don't generate webp)


The ```$args``` parameter is not required and the defaults are as follows: 

```
[
	'keep_exif' => 0, 
	'max_width' => 0,
	'max_height' => 0,
	'cmyktorgb' => 1, 
	'compression' => 'intelligent',
        'webp'        => 1, 
] 
```  

To optimize single image call the ```run()``` method on some given parameters just like the following examples:

## Single Local File
```php
use MegaOptim\Optimizer;
$megaoptim = new Optimizer('your-api-key');
$response   = $megaoptim->run( '/path/to/file.jpg', array( 'compression' => Optimizer::COMPRESSION_INTELLIGENT ) );
```

## Single URL
```php
use MegaOptim\Optimizer;
$megaoptim = new Optimizer('your-api-key');
$response   = $megaoptim->run( 'http://yoursite.com/some_image.jpg', array( 'compression' => Optimizer::COMPRESSION_INTELLIGENT ) );
```

## Multiple Files ( up to 5 )
```php
use MegaOptim\Optimizer;
$megaoptim = new Optimizer('your-api-key');
$resources = array(
	'/path/to/file1.jpg',
	'/path/to/file2.jpg',
	'/path/to/file3.jpg',
);

$response   = $megaoptim->run( $resources, array( 'compression' => Optimizer::LOSSY ) );
```

## Multiple URLs ( up to 5 )
```php
use MegaOptim\Optimizer;
$megaoptim = new Optimizer('your-api-key');
$resources = array(
	'http://somesite.com/path/to/file1.jpg',
	'http://somesite.com/path/to/file2.jpg',
	'http://somesite.com/path/to/file3.jpg',
);

$response   = $megaoptim->run( $resources, array( 'compression' => Optimizer::LOSSY ) );
```


## Handling Response

Once we run the optimization with `run()` method we have the instance of `MegaOptim\Client\Http\Response` which contains all the response variables and array of optimized image objects.

The contents of the ```$response``` methods are as follows:

- ```$response->isSuccessful()``` - Returns boolean to determine if the optimization was successful.
- ```$response->isError()``` - Returns boolean to determine if the optimization was not successful.
- ```$response->getErrors()``` - Returns ```array``` with errors.
- ```$response->getOptimizedFiles()``` - Returns array of ```optimized image objects``` from the class ```MegaOptim\Client\Http\Result[]```

To get the optimized image objects and further process them we can use the `getOptimizedFiles()` method which will return array as mentioned above:

```php
$files = $response->getOptimizedFiles();
foreach($files as $file) {
 // Do something
}
```

We have the following properties and methods available to use for each optimized image object:

- ```$file->getFileName()``` - Returns the original file name.
- ```$file->getOptimizedSize()``` - Returns the new size in bytes.
- ```$file->getSavedBytes()``` - Returns the total saved bytes.
- ```$file->getSavedPercent()``` - Returns the total saved space in percentage.
- ```$file->getUrl()``` - Returns the optimized image url. You need to download and store it on your server because it will be removed after 1 hour from MegaOptim server
- ```$file->getWebP()``` - Returns NULL if there is no webp version available or ResultWebP instance for the webp version of this file.

To save the optimized images locally we have three methods available:
- ```$file->saveOverwrite()``` - Overwrites the local file with the new optimized file. **Only available when local files are being optimized, not URLs.**
- ```$file->saveAsFile( $file_path )``` - Saves the optimized file in the specified file path. If the result directory doesn't exist it attempts to create it recursively.
- ```$file->saveToDir( $dir_path )``` - Saves the optimized file in the specified directory path. If the result directory doesn't exist it attempts to create it recursively.

```php
if( !empty($files) ) {
	// Note: Thise are for demonstration purposes, you don't have to use it like this.
	// Overwrite
	$files[0]->saveOverwrite();
	// Or save as other file
	$files[0]->saveAsFile('/path/to/other/file.jpg');
	// Or save in some other dir with the original name
	$files[0]->saveToDir('/path/to/other');
}

```

# TODO
* Test library on different Operating Systems such as Windows.

# Development
If you found a bug or want to contribute to the script feel free to create pull requests to make it even better!

## License

```
Copyright (C) 2018 MegaOptim (https://megaoptim.com)

This file is part of MegaOptim Image Optimizer

MegaOptim Image Optimizer is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

MegaOptim Image Optimizer is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with MegaOptim Image Optimizer. If not, see <https://www.gnu.org/licenses/>.
```





