<?php
namespace App\Entity;

use SDAM\Traits\HasTimestamp;

/**
 * Class Question
 *
 * @storeName questions_table
 * @factory App\Factory\QuestionFactory
 */
class Question
{
    use HasTimestamp;

    /**
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
     * @link belongsTo
     * @var Response
     */
    public $response;

    /**
     * @text
     * @var string
     */
    public $content;
}