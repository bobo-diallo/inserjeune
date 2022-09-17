<?php

namespace App\Config\Annotations\Uploadable\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class UploadableField
 * @Annotation
 * @Target("PROPERTY")
 */
class UploadableField {
	private string $filename;

	private string $path;

	public function __construct(array $options) {
		if (empty($options['filename'])) {
			throw new  \InvalidArgumentException("L'annotation Uploadfiled doit avoir un attribut 'filename'");
		}
		if (empty($options['path'])) {
			throw new  \InvalidArgumentException("L'annotation Uploadfiled doit avoir un attribut 'path'");
		}

		$this->filename = $options['filename'];
		$this->path = $options['path'];
	}

	public function getFilename(): string {
		return $this->filename;
	}

	public function getPath(): string {
		return $this->path;
	}

}
