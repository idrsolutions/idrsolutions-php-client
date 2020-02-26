# BuildVu PHP Client #

Convert PDF to HTML5 or SVG with PHP, using the BuildVu PHP Client to interact with IDRsolutions' [BuildVu Microservice Example](https://github.com/idrsolutions/buildvu-microservice-example).

The BuildVu Microservice Example is an open source project that allows you to convert PDF to HTML5 or SVG by running [BuildVu](https://www.idrsolutions.com/buildvu/) as an online service.

IDRsolutions offer a free trial service for running BuildVu with PHP, more infomation on this can be found [here](https://www.idrsolutions.com/buildvu/convert-pdf-in-php/).

For tutorials on how to deploy BuildVu to an app server, visit the [documentation](https://support.idrsolutions.com/hc/en-us/sections/360000444652-Deploy-BuildVu-to-an-app-server).

-----

# Installation #

```
composer require idrsolutions/idrsolutions-php-client
```

-----

# Usage #

For additional values to add to ```parameters```, please refer to the [API](https://github.com/idrsolutions/buildvu-microservice-example/blob/master/API.md).

## Example Conversion Script with File Upload ##
```php
<?php

require_once __DIR__ . "/PATH/TO/vendor/autoload.php";

use IDRsolutions\IDRCloudClient;

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_BUILDVU; 

$conversion_results = Converter::convert(array(
    'endpoint' => $endpoint,
    'parameters' => array(
        'input' => Converter::INPUT_UPLOAD,
        'file' => __DIR__ . 'path/to/file.pdf'
    )
));

Converter::downloadOutput($conversion_results, __DIR__ . '/');

echo $conversion_results['downloadUrl'];
```

## Example Conversion Script Passing URL to Server ##
```php
<?php

require_once __DIR__ . "/PATH/TO/vendor/autoload.php";

use IDRsolutions\IDRCloudClient;

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_BUILDVU; 

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
        └── buildvu-php-client
            └── ...
```
#### Appropriate Script Changes ####
```php
<?php

require_once __DIR__ . "/../vendor/autoload.php";

use IDRsolutions\IDRCloudClient;

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_BUILDVU; 

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
    "previewUrl": "http://localhost:8080/buildvu-microservice/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file/index.html",
    "downloadUrl": "http://localhost:8080/buildvu-microservice/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file.zip"
}
http://localhost:8080/buildvu-microservice/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file/index.html
```

## Hosted Script ##

This example uses XAMPP htdocs.

```
htdocs
├── buildvu
│   ├── composer.json
│   ├── composer.lock
│   ├── convert.php
│   └── vendor
│       ├── autoload.php
│       ├── composer
│       │   ├── ...
│       └── idrsolutions
│           └── IDRSolutions-php-client
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

$endpoint = "http://localhost:8080/" . IDRCloudClient::INPUT_BUILDVU; 

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

```localhost:80/buildvu/convert.php```

#### Output ####

The webpage will display the link to the preview:

```http://localhost:8080/buildvu-microservice/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file/index.html```

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
