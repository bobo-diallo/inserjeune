<?php

namespace App\Event\Company;

use Symfony\Contracts\EventDispatcher\Event;

class CompanyWasUpdatedEvent extends Event {
    public const NAME = 'company.was_updated';

    private int $companyId;

    public function __construct(int $companyId) {
        $this->companyId = $companyId;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }
}