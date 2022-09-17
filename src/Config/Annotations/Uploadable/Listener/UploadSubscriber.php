<?php

namespace App\Config\Annotations\Uploadable\Listener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use UploadBundle\Annotation\UploadAnnotationReader;
use UploadBundle\Handler\UploadHandler;

class UploadSubscriber implements EventSubscriber {

	/**
	 * @var UploadAnnotationReader
	 */
	private UploadAnnotationReader $reader;
	/**
	 * @var UploadHandler
	 */
	private UploadHandler $uploadHandler;

	public function __construct(UploadAnnotationReader $reader, UploadHandler $uploadHandler) {
		$this->reader = $reader;
		$this->uploadHandler = $uploadHandler;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents() {
		return [
			'prePersist',
			'preUpdate',
			'postLoad',
			'postRemove'
		];
	}

	/**
	 * @param EventArgs $event
	 * @throws \Exception
	 */
	public function prePersist(EventArgs $event) {
		$this->preEvent($event);
	}

	/**
	 * @param EventArgs $event
	 * @throws \Exception
	 */
	private function preEvent(EventArgs $event) {
		$entity = $event->getEntity();
		foreach ($this->reader->getUploadableFields($entity) as $property => $annotation) {
			$this->uploadHandler->UploadFile($entity, $property, $annotation);
		}
	}

	/**
	 * @param EventArgs $event
	 * @throws \Exception
	 */
	public function preUpdate(EventArgs $event) {
		$this->preEvent($event);
	}

	/**
	 * @param EventArgs $eventArgs
	 * @throws \Exception
	 */
	public function postLoad(EventArgs $eventArgs) {
		$entity = $eventArgs->getEntity();
		foreach ($this->reader->getUploadableFields($entity) as $property => $annotation) {
			$this->uploadHandler->setFileFromFilename($entity, $property, $annotation);
		}
	}

	/**
	 * @param EventArgs $eventArgs
	 * @throws \Exception
	 */
	public function postRemove(EventArgs $eventArgs) {
		$entity = $eventArgs->getEntity();
		foreach ($this->reader->getUploadableFields($entity) as $property => $annotation) {
			$this->uploadHandler->removeFile($entity, $property);
		}
	}
}
