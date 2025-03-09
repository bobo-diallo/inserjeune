<?php declare(strict_types=1);

namespace App\Repository;

use App\Model\Template\TemplateEntity;

interface ParentColumTemplateRepository {
	/**
	 * @return TemplateEntity[]
	 */
	public function getTemplateData(): array;
}
