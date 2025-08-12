<?php

namespace App\Event\Listener\PersonDegree;

use App\Entity\PersonDegree;
use App\Event\PersonDegree\PersonDegreeWasUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: PersonDegreeWasUpdatedEvent::NAME, method: 'onPersonDegreeWasUpdated')]
class PersonDegreeUpdateListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onPersonDegreeWasUpdated(PersonDegreeWasUpdatedEvent $event): void
    {
        $personDegree = $this->entityManager->getRepository(PersonDegree::class)->find($event->getPersonDegreeId());
        $user = $personDegree->getUser();

        if ($user) {
            $user->setEmail($personDegree->getEmail());
             // $user->setPhone($personDegree->getPhoneHome());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}