<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader {
	private string $targetDirectory;
	private SluggerInterface $slugger;
	private string $avatarsDirectory;

	public function __construct(
		$targetDirectory,
		$avatarsDirectory,
		SluggerInterface $slugger
	) {
		$this->targetDirectory = $targetDirectory;
		$this->slugger = $slugger;
		$this->avatarsDirectory = $avatarsDirectory;
	}

	public function upload(UploadedFile $file, ?string $oldFilename = null): string {
		return $this->saveFile($file, $this->getTargetDirectory(), $oldFilename);
	}

	public function uploadAvatar(UploadedFile $file, ?string $oldFilename = null): string {
		return $this->saveFile($file, $this->avatarsDirectory(), $oldFilename);
	}


	public function getTargetDirectory(): string {
		return $this->targetDirectory;
	}

	public function avatarsDirectory(): string {
		return $this->avatarsDirectory;
	}

	private function saveFile(UploadedFile $file, string $targetDirectory, ?string $oldFilename = null): string {
		if ($oldFilename) {
			$this->removeOldFile($oldFilename, $targetDirectory);
		}

		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFilename = $this->slugger->slug($originalFilename);
		$fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

		try {
			$file->move($targetDirectory, $fileName);
		} catch (FileException $e) {
		}

		return $fileName;
	}

	public function removeOldFile(string $oldFilename, ?string $targetDirectory = null): void {
		if (!$targetDirectory) {
			$targetDirectory = $this->getTargetDirectory();
		}

		$file_path = $targetDirectory . DIRECTORY_SEPARATOR . $oldFilename;

		if (file_exists($file_path)) {
			unlink($file_path);
		}
	}
}
