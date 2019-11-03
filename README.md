[![Build Status](https://travis-ci.org/francis94c/refactor-ci.svg?branch=master)](https://travis-ci.org/francis94c/refactor-ci) [![Coverage Status](https://coveralls.io/repos/github/francis94c/refactor-ci/badge.svg?branch=master)](https://coveralls.io/github/francis94c/refactor-ci?branch=master) [![Maintainability](https://api.codeclimate.com/v1/badges/29e49c05a1d1404f365d/maintainability)](https://codeclimate.com/github/francis94c/refactor-ci/maintainability) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/francis94c/refactor-ci/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/francis94c/refactor-ci/?branch=master)

# refactor-ci

When building RESTful APIs, there are certain structures you want your JSON payloads to take up which do not necessarily reflect the way they were retrieved from a database, etc. or simply perform extra data retrievals before they are sent as response.

This library basically refactor your associative arrays using specified rules. I'll demonstrate this with examples below in the Usage section.

This library plays a similar role to the Laravel Resources API. Only this is
built to suit Code Igniter specifically.

## Installation ##

To install, download and install Splint from <https://splint.cynobit.com/downloads/splint> and then run the below from your Code Igniter project root

```bash
splint install francis94c/refactor-ci
```

## Loading ##

From anywhere you can access the ```CI``` instance

```php
$this->load->package("francis94c/refactor-ci");
```

## Usage ##

There are two ways to use this library.

## Method 1 ##
Create a `refactor.php` file in your (Code Igniter) application config folder.

Though this is not necessary but it makes sense to save your refactor rules in a separate file other than the Code Igniter (application) config file.

This means you can create config rules also in the application config file, But not
the best of practices.

The library when loaded, will always attempt to load the `refactor.php` config file. No error will be thrown if this file doesn't exist.

Below is a sample `refactor.php` file with rules defined.

```php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['refactor_email'] = [
  'unset' => [
    'user_id',
    'hit_count',
    'last_read_time',
    'last_message',
  ],
  'replace' => [
    'open_count'  => 'times_opened',
    'stuck_count' => 'stuck_tx'
  ],
  'bools' => [
    'active'
  ],
  'cast' => [
    'id' => 'int'
  ],
  'inflate' => [
    'users_ids' => [
      'table'    => 'users',
      'refactor' => 'user'
    ]
  ]
];

$config['refactor_user'] = [
  'unset' => [
    'date_registered',
    'lua_script'
  ],
  'cast' => [
    'service_status' => 'int'
  ],
];
````
## Method 2 ##
This methods requires you to create as much PHP classes as the JSON payloads you
want to modify.

These PHP classes should be created in the `application/libraries/refactor`.

Notices that you have to create a `refactor` class in you `libraries` folder.

Lets say I have a payload to return as a response from an API end point which is
a user data array as below.
```php
$data = [
  'name'    => 'John Doe',
  'email'   => 'john.doe@example.com',
  'address' => 'Utopia'
];
```
And I want every `User` payload like the above to have a `status` key a bd a value of `true`.

I'll creates a say `User.php` file in the `application/libraries/refactor` with a
`User` class defined within it and extending/inherit from the `RefactorPayload`
class. Then override the `toArray` method, returning an array as shown below.

```php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends RefactorPayload
{

  public function toArray():array
  {
    $this->status = true;
    return parent::toArray();
  }
}
```

From the above, it is clear that the `Email` object which extebds the `RefactorPayload`
class is kind of the payload itself.

Proof of the is in the `toArray` function where it's `status` field was set to `true`
and then a call to the `parent` `toArray` function being made to return itself as an array.

To actual effect the `toArray` method on an actual payload, we load the refactor
library and use as below.

```php
$this->load->package('francis94c/refactor-ci');

$data = [
  'name'    => 'John Doe',
  'email'   => 'john.doe@example.com',
  'address' => 'Utopia'
];

$data = $this->refactor->payload(Email::class, $data);
```

The above `payload` function will create an instance of the class name provided as it's first argument and run it's `toArray` method on the second argument provided.

If the payload is an array (multiple user data), process it as below.
```php
$data = $this->refactor->array(Email::class, $data);
```
