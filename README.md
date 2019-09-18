[![Build Status](https://travis-ci.org/francis94c/refactor-ci.svg?branch=master)](https://travis-ci.org/francis94c/refactor-ci) [![Coverage Status](https://coveralls.io/repos/github/francis94c/refactor-ci/badge.svg?branch=master)](https://coveralls.io/github/francis94c/refactor-ci?branch=master) [![Maintainability](https://api.codeclimate.com/v1/badges/29e49c05a1d1404f365d/maintainability)](https://codeclimate.com/github/francis94c/refactor-ci/maintainability) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/francis94c/refactor-ci/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/francis94c/refactor-ci/?branch=master)

# refactor-ci

When building RESTful APIs, there are certain structures you want your JSON payloads to take up which do not necessarily reflect the way they were retrieved from a database, etc.

This library basically refactor you associative arrays using specified rules. I'll demonstrate this with examples below in the Usage section.

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

To use, It's best to create a `refactor.php` file in the application config folder of Code Igniter. the ends result would be `application/config/refactor.php`.

Though this is not necessary but it makes sense to save your refactor rules in a separate file other than the Code Igniter (application) config file.

This means you can create config rules also in the application config file, But not advisable.

The library when loaded, attempts to load the `refactor.php` config file. No error will be thrown if this file doesn't exist.

Below is a sample `refactor.php` file wit sample rules.

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
