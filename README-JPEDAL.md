# JPEDAL PHP Client #

Convert PDF to images, using the JPedal PHP Client to interact with IDRsolutions' [JPedal Microservice Example](https://github.com/idrsolutions/jpedal-microservice-example).

The JPedal Microservice Example is an open source project that allows you to convert PDF to images by running [JPedal](https://www.idrsolutions.com/jpedal/) as an online service.

IDRsolutions offer a free trial service for running JPedal with PHP, more infomation on this can be found [here](https://www.idrsolutions.com/JPedal/convert-pdf-in-php/).

# Installation #

```
composer require idrsolutions/idrsolutions-php-client
```

-----

# Usage #

For additional values to add to ```parameters```, please refer to the [API](https://github.com/idrsolutions/jpedal-microservice-example/blob/master/API.md).

## Example Conversion Script with File Upload ##
```php
<?php

require_once __DIR__ . "/PATH/TO/vendor/autoload.php";

use IDRsolutions\IDRCloudClient;

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_JPEDAL;  

$conversion_results = Converter::convert(array(
    'endpoint' => $endpoint,
    'parameters' => array(
        'input' => Converter::INPUT_UPLOAD,
        'file' => __DIR__ . 'path/to/file.pdf'
    )
));

Converter::downloadOutput($conversion_results, __DIR__ . '/');

echo $conversion_results['downloadUrl'];
?>
```

## Example Conversion Script Passing URL to Server ##
```php
<?php

require_once __DIR__ . "/PATH/TO/vendor/autoload.php";

use IDRsolutions\IDRCloudClient;

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_JPEDAL;  

$conversion_results = Converter::convert(array(
    'endpoint' => $endpoint,
    'parameters' => array(
        'input' => Converter::INPUT_DOWNLOAD,
        'url' => 'http://path.to/file.pdf'
    )
));

Converter::downloadOutput($conversion_results, __DIR__ . '/');

echo $conversion_results['downloadUrl'];
```

## Command Line ##
```
myproject/
├── composer.json
├── composer.lock
├── conversion_location
│   ├── convert.php
│   ├── input_files
│   │   └── file.pdf
│   └── output
└── vendor
    ├── autoload.php
    ├── composer
    │   └── ...
    └── idrsolutions
        └── IDRSolutions-php-client
            └── ...
```
#### Appropriate Script Changes ####
```php
<?php

require_once __DIR__ . "/../vendor/autoload.php";

use IDRsolutions\IDRCloudClient;

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_JPEDAL;  

$conversion_results = Converter::convert(array(
    'endpoint' => $endpoint,
    'parameters' => array(
        'input' => Converter::INPUT_UPLOAD,
        'file' => __DIR__ . 'input_files/file.pdf'
    )
));

Converter::downloadOutput($conversion_results, __DIR__ . '/output/');

echo $conversion_results['downloadUrl'];
```

#### Execute ####

```
cd conversion_location
php convert.php
```
#### Output ####

```
{
    "state": "processing"
}
{
    "state": "processed",
    "downloadUrl": "http://localhost:8080/jpedal/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file.zip"
}
http://localhost:8080/jpedal/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file/index.html
```

## Hosted Script ##

This example uses XAMPP htdocs.

```
htdocs
├── jpedal
│   ├── composer.json
│   ├── composer.lock
│   ├── convert.php
│   └── vendor
│       ├── autoload.php
│       ├── composer
│       │   ├── ...
│       └── idrsolutions
│           └── jpedal-php-client
│               └── ...
└── conversion
    ├── input_files
    │   └── file.pdf
    └── output
```

#### Appropriate Script Changes ####
```php
<?php

require_once __DIR__ . "/vendor/autoload.php";

use IDRsolutions\IDRCloudClient;

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_JPEDAL;  

try {
    $conversion_results = Converter::convert(array(
        'endpoint' => $endpoint,
        'parameters' => array(
            'input' => Converter::INPUT_UPLOAD,
            'file' => __DIR__ . '/../conversion/input_files/file.pdf'
        )
    ));
    
    Converter::downloadOutput($conversion_results, __DIR__ . '/../conversion/output');
    
    echo $conversion_results['downloadUrl'];
    
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTrace();
    exit(1);
}
```

#### Execution ####

In this case, the Apache server is deployed at localhost:80. To execute the script, visit:

```localhost:80/jpedal/convert.php```

#### Output ####

The webpage will display the link to the preview:

```http://localhost:8080/jpedal/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file/index.html```

The downloaded zip will be available in ```htdocs/conversion/output```.

# Who do I talk to? #

Found a bug, or have a suggestion / improvement? Let us know through the Issues page.

Got questions? You can contact us [here](https://idrsolutions.zendesk.com/hc/en-us/requests/new).

-----

Copyright 2018 IDRsolutions

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

[http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
