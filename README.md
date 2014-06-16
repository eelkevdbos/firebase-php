firebase-php
============

[![Build Status](https://travis-ci.org/eelkevdbos/firebase-php.svg?branch=master)](https://travis-ci.org/eelkevdbos/firebase-php) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eelkevdbos/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/eelkevdbos/firebase-php/?branch=master)

Firebase php wrapper for REST API

## Installation
Add the following line to your composer.json and run `composer update`:

```
{
  "require": {
    "eelkevdbos/firebase-php": "dev-master"
  }
}
```

## Basic Usage
By setting your firebase secret as token, you gain superuser access to firebase.

```php
$fb = new Firebase\Firebase(array(
  'base_url' => YOUR_FIREBASE_BASE_URL,
  'token' => YOUR_FIREBASE_SECRET,
),new GuzzleHttp\Client());

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
$fbTokenGenerator = new Firebase\Auth\TokenGenerator(YOUR_FIREBASE_SECRET);

$fb = new Firebase\Firebase(array(
  'base_url' => YOUR_FIREBASE_BASE_URL
),new GuzzleHttp\Client());

$fb->setOption('token', $fbTokenGenerator->generateToken(array('email' => 'test@example.com'));
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
