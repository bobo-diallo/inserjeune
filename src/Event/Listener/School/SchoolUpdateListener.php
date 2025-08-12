<?php

namespace App\Event\Listener\School;

use App\Entity\School;
use App\Event\School\SchoolWasUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: SchoolWasUpdatedEvent::NAME, method: 'onSchoolWasUpdated')]
class SchoolUpdateListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onSchoolWasUpdated(SchoolWasUpdatedEvent $event): void
    {
        $school = $this->entityManager->getRepository(School::class)->find($event->getSchoolId());
        $user = $school->getUser();

        if ($user) {
            $user->setEmail($school->getEmail());
            $user->setPhone($school->getPhoneStandard());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}