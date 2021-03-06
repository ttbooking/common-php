<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 6/9/2016
 * Time: 1:30 PM
 */

namespace Common\ModelReflection;
use Common\Util\Validation;
use Common\ModelReflection\Enum\AnnotationEnum;

class ModelClass {

	/**
	 * @var string
	 */
	private $className;

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var string
	 */
	private $rootName;

    /**
     * @var string
     */
    private $rootEncoding;

	/**
	 * @var ModelProperty[]
	 */
	private $properties;

	/**
	 * @var DocBlock
	 */
	private $docBlock;

	/**
	 * ModelClassData constructor.
	 * @param object $model
	 */
	public function __construct($model) {
		if(!is_object($model)) {
			throw new \InvalidArgumentException('A model must be an object.');
		}
		$reflectionClass = new \ReflectionClass($model);
		$this->docBlock = new DocBlock($reflectionClass->getDocComment());
		$this->className = $reflectionClass->getName();
		$this->namespace = $reflectionClass->getNamespaceName();

		$this->rootName = lcfirst($reflectionClass->getShortName());
		if($this->docBlock->hasAnnotation(AnnotationEnum::XML_ROOT) && !Validation::isEmpty($this->docBlock->getAnnotation(AnnotationEnum::XML_ROOT))) {
			$this->rootName = $this->docBlock->getFirstAnnotation(AnnotationEnum::XML_ROOT);
		}

        if($this->docBlock->hasAnnotation(AnnotationEnum::XML_ENCODING) && !Validation::isEmpty($this->docBlock->getAnnotation(AnnotationEnum::XML_ENCODING))) {
            $this->rootEncoding = $this->docBlock->getFirstAnnotation(AnnotationEnum::XML_ENCODING);
        }

		$properties = $reflectionClass->getProperties();
        if(count($properties) == 0) {
            throw new \InvalidArgumentException('The model class ' . $reflectionClass->getName() . ' has no properties defined.');
        }

		foreach($properties as $property) {
			$property->setAccessible(true);
			$modelProperty = new ModelProperty($property, $model, $this->namespace);
            $this->properties[] = $modelProperty;
		}
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function getRootName()
	{
		return $this->rootName;
	}

    /**
     * @return string
     */
	public function getRootEncoding()
    {
	    return $this->rootEncoding;
    }

	/**
	 * @return ModelProperty[]
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @return DocBlock
	 */
	public function getDocBlock()
	{
		return $this->docBlock;
	}

	public static function instantiate($modelClassName) {
	    try {
            $reflectionClass = new \ReflectionClass($modelClassName);
            $model = $reflectionClass->newInstanceWithoutConstructor();
        }
        catch(\Exception $ex) {
            throw new \InvalidArgumentException('Could not instantiate model ' . $modelClassName . '. ' . $ex->getMessage());
        }

        return $model;
    }
}