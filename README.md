[![Build Status](https://travis-ci.org/TuxBoy/Migration.svg?branch=master)](https://travis-ci.org/TuxBoy/Migration)

# Migration 

Manage the database migrations for your PHP app, this library was made for your migration to be launched
automatically in your application

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
        \TuxBoy\Config::ENTITY_PATH => 'App\Entity\\'
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

