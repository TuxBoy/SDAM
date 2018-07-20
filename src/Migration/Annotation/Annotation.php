<?php

namespace TuxBoy\Annotation;

use Exception;
use PhpDocReader\PhpDocReader;
use ReflectionClass;
use ReflectionException;

/**
 * ReflectionAnnotation.
 */
class Annotation
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $docComment;

    /**
     * @var ReflectionClass
     */
    private $argument;

    /**
     * @var null|string
     */
    private $propertyName;

    /**
     * @var PhpDocReader
     */
    private $reader;

    /**
     * Annotation constructor
     *
     * @param string|ReflectionClass $argument Le nom de la classe ou l'object que l'on souhaite récupérer les annotations
     * @param string|null $propertyName
     * @throws ReflectionException
     */
    public function __construct($argument, string $propertyName = null)
    {
        $this->argument     = is_string($argument) ? new ReflectionClass($argument) : $argument;
        $this->propertyName = $propertyName;
        $this->reader       = new PhpDocReader();
        // Si le property_name est null, alors on souhaite obtenir les annotations de la classe
        $this->docComment = null === $this->propertyName
            ? $this->argument->getDocComment()
            : $this->argument->getProperty($this->propertyName)->getDocComment();
    }

    /**
     * Récupère l'annotation de la proriété demandé.
     *
     * @param string $annotationName
     *
     * @throws Exception
     *
     * @return Annotation
     */
    public function getAnnotation(string $annotationName): self
    {
        list($this->name, $this->value) = $this->parseDocComment($annotationName);

        return $this;
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function getAnnotations(): array
    {
        return $this->parseDocComment();
    }

    /**
     * Vérifie si l'annotation passé en paramètre existe.
     *
     * @param string $annotationName
     *
     * @return bool
     * @throws Exception
     */
    public function hasAnnotation(string $annotationName): bool
    {
        return in_array($annotationName, $this->parseDocComment($annotationName), true);
    }

    /**
     * Parse la phpdoc pour y extraire le nom de l'annotation de la proriété en question et sa
     * valeur (facultatif).
     *
     * @param string $annotationName Le nom de l'annotation à parser, si null alors retourne toutes
     * les annotations de la propriété
     *
     * @throws Exception
     *
     * @return array|null
     */
    private function parseDocComment(?string $annotationName = null): ?array
    {
        $docComment   = $this->docComment;
        $commentsDocs = array_filter(explode('*', $docComment), function ($annotation) {
            return !empty(trim($annotation));
        });
        $annotations = array_filter(array_map(function ($annotation) {
            return trim(trim($annotation, '/'));
        }, $commentsDocs), function ($item) {
            return preg_match('#@.+#', $item, $annotations);
        });
        if ($annotationName) {
            $getAnnotationValue = current(array_filter($annotations, function ($item) use ($annotationName) {
                return preg_match('#@' . $annotationName . '.+#', $item);
            }));
            // L'annotation sans valeur @example @length
            $getAnnotation = current(array_filter($annotations, function ($item) use ($annotationName) {
                return preg_match('#@' . $annotationName . '#', $item);
            }));
            $name  = null;
            $value = null;
            if ($getAnnotationValue) {
                list($name, $value) = explode(' ', $getAnnotationValue);
            } elseif ($getAnnotation) {
                list($name) = explode(' ', $getAnnotation);
            }
        } else {
            $annotationsList = [];
            foreach ($annotations as $annotation) {
                $parts = explode(' ', $annotation);
                if (count($parts) > 1) {
                    [$name, $value] = $parts;
                } else {
                    list($name, ) = $parts;
                    $value = null;
                }
                $annotationsList[str_replace('@', '', $name)] = $value;
            }
            return $annotationsList;
        }
        return [str_replace('@', '', $name), $value];
    }

    /**
     * Récupère la valeur de l'anotation s'il y en a une.
     *
     * @return null|string
     * @throws \PhpDocReader\AnnotationException
     */
    public function getValue(): ?string
    {
        return $this->reader->getPropertyClass($this->argument->getProperty($this->propertyName)) ?? $this->value;
    }

    /**
     * Récupère le nom de l'annotation.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param $argument string|ReflectionClass
     * @param null|string $propertyName
     * @return Annotation
     * @throws ReflectionException
     */
    public static function of($argument, ?string $propertyName = null): self
    {
        return new Annotation($argument, $propertyName);
    }
}