<?php

namespace App\Event\PersonDegree;

use Symfony\Contracts\EventDispatcher\Event;

class PersonDegreeWasUpdatedEvent extends Event {
    public const NAME = 'personDegree.was_updated';

    private int $personDegreeId;

    public function __construct(int $personDegreeId) {
        $this->personDegreeId = $personDegreeId;
    }

    public function getPersonDegreeId(): int
    {
        return $this->personDegreeId;
    }
}