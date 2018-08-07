<?php
namespace SDAM\Traits;

use DateTime;

/**
 * Trait HasCreatedAt
 */
trait HasCreatedAt
{

    /**
	 * @default now
     * @var DateTime
     */
    public $createdAt;

}