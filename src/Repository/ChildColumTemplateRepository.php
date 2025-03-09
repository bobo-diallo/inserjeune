<?php declare(strict_types=1);

namespace App\Repository;

interface ChildColumTemplateRepository {
	/**
	 * @param int $id
	 * @return string[]
	 */
	public function getNameByParentId(int $id): array;
}
