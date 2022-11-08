<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader {
	private string $targetDirectory;
	private SluggerInterface $slugger;

	public function __construct($targetDirectory, SluggerInterface $slugger) {
		$this->targetDirectory = $targetDirectory;
		$this->slugger = $slugger;
	}

	public function upload(UploadedFile $file, ?string $oldFilename = null): string {
		if ($oldFilename) {
			$this->removeOldFile($oldFilename);
		}

		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFilename = $this->slugger->slug($originalFilename);
		$fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

		try {
			$file->move($this->getTargetDirectory(), $fileName);
		} catch (FileException $e) {
		}

		return $fileName;
	}

	public function getTargetDirectory(): string {
		return $this->targetDirectory;
	}

	public function removeOldFile(string $oldFilename): void {
		$file_path = $this->getTargetDirectory() . DIRECTORY_SEPARATOR . $oldFilename;

		if (file_exists($file_path)) {
			unlink($file_path);
		}
	}
}
