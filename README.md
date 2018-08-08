[![Build Status](https://travis-ci.org/TuxBoy/SDAM.svg?branch=master)](https://travis-ci.org/TuxBoy/Migration)

# SDAM (Simple Database Auto Migration) 

Manage the database migrations for your PHP app, this library was made for your migration to be launched
automatically in your application

There is a demo of use [here](https://github.com/TuxBoy/Migration-demo)

## Installation

````json

    "require": {
        "tuxboy/sdam": "dev-master",
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "git@github.com:tuxboy/sdam.git"
        }
    ],
````

## How to use it

````php
\SDAM\Config::current()->configure(
    [
        \SDAM\Config::DATABASE => [
            'dbname'   => 'database_name',
            'user'     => 'root',
            'password' => '',
            'host'     => 'localhost',
            'driver'   => 'pdo_mysql',
        ], // OR use .env for the database config
        \SDAM\Config::ENV_FILE        => 'path/your/.env'
        \SDAM\Config::ENTITY_PATH     => 'App\Entity\\',
        \SDAM\Config::AUTO_DROP_FIELD => false, // Optional (default value is true)
    ]
);

// Run migration engine in your app
$maintainer = new Maintainer([Entity::class]);
$maintainer->run();

// OR use middleware class
$maintainer = new \SDAM\Middleware\MaintainerMiddleware([\App\Entity\Post::class], $config);
$app->pipe($maintainer);
````

Middleware are constructed with these parameters

* Entities list, **string[]**
* $config, **string[]**

## Usage

Create your Entity class, Post entity for example :

```php
namespace App\Entity;

/**
 * Post
 *
 * @storeName custom_table_name
 */
class Post
{

    /**
     * @length 60
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $slug;

    /**
     * @store false
     * @var string
     */
    public $tmp_property;

    /**
     * @text
     * @var string
     */
    public $content;
        
}
```
(*You can see the list of possible annotations in the class AnnotationsName*)

Just start the migration, either by a simple F5 if you have it enabled in your application (middleware) or other.
The table will be created in your database.

## Todo

- [ ] Manage relationship belongsToMany
