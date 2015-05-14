firebase-php
============

[![Build Status](https://travis-ci.org/eelkevdbos/firebase-php.svg?branch=master)](https://travis-ci.org/eelkevdbos/firebase-php) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eelkevdbos/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/eelkevdbos/firebase-php/?branch=master)[![Code Coverage](https://scrutinizer-ci.com/g/eelkevdbos/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/eelkevdbos/firebase-php/?branch=master)

Firebase php wrapper for REST API

##Prerequisites
- PHP >= 5.4
- Firebase >= 1.1.1
- Composer (recommended, not required)

## Installation using composer (recommended)
Set your projects minimum stability to `dev` in `composer.json`. This is caused by the PHP-JWT dependency. After updating the composer.json file, simply execute: `composer require eelkevdbos/firebase-php dev-master`

##Installation without composer
For a vanilla install, the following dependencies should be downloaded:
- firebase/php-jwt [github](https://github.com/firebase/php-jwt/releases/tag/v1.0.0)
- guzzlehttp/guzzle [github](https://github.com/guzzle/guzzle/releases/tag/5.0.3)

Loading the dependencies can be achieved by using any [PSR-4 autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md).

## Basic Usage
By setting your firebase secret as token, you gain superuser access to firebase.

```php

use Firebase\Firebase;

$fb = Firebase::initialize(YOUR_FIREBASE_URL, YOUR_FIREBASE_SECRET);

//or set your own implementation of the ClientInterface as second parameter of the regular constructor
$fb = new Firebase([ 'base_url' => YOUR_FIREBASE_BASE_URL, 'token' => YOUR_FIREBASE_SECRET ], new GuzzleHttp\Client());

//retrieve a node
$nodeGetContent = $fb->get('/node/path');

//set the content of a node
$nodeSetContent = $fb->set('/node/path', array('data' => 'toset'));

//update the content of a node
$nodeUpdateContent = $fb->update('/node/path', array('data' => 'toupdate'));

//delete a node
$nodeDeleteContent = $fb->delete('/node/path');

//push a new item to a node
$nodePushContent = $fb->push('/node/path', array('name' => 'item on list'));

```

## Advanced Usage
For more finegrained authentication, have a look at the [security rules](https://www.firebase.com/docs/security/security-rules.html). Using the token generator allows you to make use of the authentication services supplied by Firebase.

```php

use Firebase\Firebase;
use Firebase\Auth\TokenGenerator;

$tokenGenerator = new TokenGenerator(YOUR_FIREBASE_SECRET);

$token = $tokenGenerator->generateToken(['email' => 'test@example.com'])

$fb = Firebase::initialize(YOUR_FIREBASE_BASE_URL, $token);
```

The above snippet of php interacts with the following security rules:

```
{
  "rules": {
    ".read": "auth.email == 'test@example.com'"
    ".write": "auth.email == 'admin@example.com'"
  }
}
```
And will allow the snippet read-access to all of the nodes, but not write-access.

##Concurrent requests
Execution of concurrent requests can be achieved with the same syntax as regular requests. Simply wrap them in a Closure and call the closure via the `batch` method and you are all set.

```php

use Firebase\Firebase;

$fb = Firebase::initialize(YOUR_FIREBASE_BASE_URL, YOUR_FIREBASE_SECRET);

$requests = $fb->batch(function ($client) {
    for($i = 0; $i < 100; $i++) {
        $client->push('list', $i);
    }
});

$pool = new GuzzleHttp\Pool($fb->getClient(), $requests);
$pool->wait();

```

## Integration
At the moment of writing, integration for Laravel 4.* is supported. A service provider and a facade class are supplied. Installation is done in 2 simple steps after the general installation steps:

1. edit `app/config/app.php` to add the service provider and the facade class
```
    'providers' => array(
      ...
      'Firebase\Integration\Laravel\FirebaseServiceProvider'
    )
    
    'aliases' => array(
      ...
      'Firebase' => 'Firebase\Integration\Laravel\Firebase'
    )
```
2. edit `app/config/services.php` (supplied by default from L4.2) to add `token` and `base_url` settings
```
    'firebase' => array(
      'base_url' => YOUR_FIREBASE_BASE_URL,
      'token' => YOUR_FIREBASE_SECRET
    )
```

##Eventing

The library supports the EventEmitter pattern. The event-emitter is attached to the Firebase class. Events currently available:
- RequestsBatchedEvent
