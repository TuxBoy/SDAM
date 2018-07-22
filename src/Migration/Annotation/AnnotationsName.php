<?php
namespace TuxBoy\Annotation;

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
     * @var string|boolean
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
     * @link belongsTo
     */
    const P_LINK = 'link';

    /**
     * Text value annotation, take as parameter the method to execute to define a default value in the field
     *
     * @default defaultMethod
     */
    const P_DEFAULT = 'default';

    /**
     * Text value annotation, the name of the table that will be created
     *
     * @storeName table_name
     */
    const C_STORE_NAME = 'storeName';

}