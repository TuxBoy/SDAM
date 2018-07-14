<?php
namespace TuxBoy;

abstract class Mapping
{

    public static $fields = [
        'string' => 'varchar',
        'boolean' => 'tinyint'
    ];

}