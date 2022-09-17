<?php

namespace App\Config\Annotations\Uploadable\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;

class UploadAnnotationReader {
	/**
	 * @var AnnotationReader
	 */
	private AnnotationReader $reader;

	public function __construct(AnnotationReader $reader) {
		$this->reader = $reader;
	}

	/**
	 * @param $entity
	 * @return array
	 * @throws \ReflectionException
	 */
	public function getUploadableFields($entity) {
		$reflection = new  \ReflectionClass(get_class($entity));
		if (!$this->isUploadable($entity)) {
			return [];
		}
		$properties = [];
		foreach ($reflection->getProperties() as $property) {
			$annotation = $this->reader->getPropertyAnnotation($property, UploadableField::class);
			if ($annotation !== null) {
				$properties[$property->getName()] = $annotation;
			}
		}
		return $properties;
	}

	/**
	 * @param $entity
	 * @return bool
	 * @throws \ReflectionException
	 */
	public function isUploadable($entity) {
		$reflection = new \ReflectionClass(get_class($entity));
		return $this->reader->getClassAnnotation($reflection, Uploadable::class) !== null;
	}
}
