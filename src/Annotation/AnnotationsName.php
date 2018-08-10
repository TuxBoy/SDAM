<?php
namespace SDAM\Annotation;

use SDAM\Method\Methods;

/**
 * AnnotationsName
 *
 * Annotation list for the migration system in the entity
 *
 * Property annotations are prefixed by P_ and class annotation is prefixed by C_
 */
abstract class AnnotationsName
{
    /**
     * Value annotation, a basic field type :
     * string : VARCHAR
     * boolean : TINYINT
     * integer : INT
     *
     * @var string|boolean|datetime
     * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/reference/types.html#types
     */
    const P_VAR = 'var';

    /**
     * Boolean annotation, for the longtext field type
     *
     * @text
     */
    const P_TEXT = 'text';

    /**
     * Integer annotation value, set the size of the field in database
     *
     * @length 60
     */
    const P_LENGTH = 'length';

    /**
     * Text value annotation for the relation type
     *
     * @link belongsTo|belongsToMany
     */
    const P_LINK = 'link';

    /**
     * Text value annotation, take as parameter the method to execute to define a default value in the field
     *
     * @default defaultMethod
	 * @see Methods const for default method
     */
    const P_DEFAULT = 'default';

    /**
     * Specifies that a property will not be persisted in database
     *
     * @store false|true
     */
    const P_STORE = 'store';

    /**
     * Text value annotation, the name of the table that will be created
     *
     * @storeName table_name
     */
    const C_STORE_NAME = 'storeName';

}