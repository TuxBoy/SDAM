[![Build Status](https://travis-ci.org/TuxBoy/Migration.svg?branch=master)](https://travis-ci.org/TuxBoy/Migration)

# Migration 

Manage the database migrations for your PHP app, this library was made for your migration to be launched
automatically in your application

There is a demo of use [here](https://github.com/TuxBoy/Migration-demo)

## Installation

````json

    "require": {
        "tuxboy/migration": "dev-master",
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "git@github.com:tuxboy/migration.git"
        }
    ],
````

## How to use it

````php
\TuxBoy\Config::current()->configure(
    [
        \TuxBoy\Config::DATABASE => [
            'dbname'   => 'database_name',
            'user'     => 'root',
            'password' => '',
            'host'     => 'localhost',
            'driver'   => 'pdo_mysql',
        ],
        \TuxBoy\Config::ENTITY_PATH => 'App\Entity\\',
        \TuxBoy\Config::AUTO_DROP_FIELD => false, // Optional (default value is true)
    ]
);

// Run migration engine in your app
$maintainer = new Maintainer([Entity::class]);
$maintainer->run();

// OR use middleware class
$maintainer = new \TuxBoy\Middleware\MaintainerMiddleware([\App\Entity\Post::class], $config);
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