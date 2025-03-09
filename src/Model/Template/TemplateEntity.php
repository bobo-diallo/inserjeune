<?php declare(strict_types=1);

namespace App\Model\Template;

final class TemplateEntity {
	private int $id;
	private string $name;

	/**
	 * @param int $id
	 * @param string $name
	 */
	public function __construct(int $id, string $name) {
		$this->id = $id;
		$this->name = $name;
	}

	public function id(): int {
		return $this->id;
	}

	public function name(): string {
		return $this->name;
	}

}
