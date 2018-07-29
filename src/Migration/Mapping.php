<?php
namespace SDAM;

abstract class Mapping
{

    public static $fields = [
        'string' => 'varchar',
        'boolean' => 'tinyint'
    ];

}