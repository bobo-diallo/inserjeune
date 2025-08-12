<?php

namespace App\Event\School;

use Symfony\Contracts\EventDispatcher\Event;

class SchoolWasUpdatedEvent extends Event {
    public const NAME = 'school.was_updated';

    private int $schoolId;

    public function __construct(int $schoolId) {
        $this->schoolId = $schoolId;
    }

    public function getSchoolId(): int
    {
        return $this->schoolId;
    }
}