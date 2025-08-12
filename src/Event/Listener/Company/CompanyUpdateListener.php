<?php

namespace App\Event\Listener\Company;

use App\Entity\Company;
use App\Event\Company\CompanyWasUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: CompanyWasUpdatedEvent::NAME, method: 'onCompanyWasUpdated')]
class CompanyUpdateListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onCompanyWasUpdated(CompanyWasUpdatedEvent $event): void
    {
        $company = $this->entityManager->getRepository(Company::class)->find($event->getCompanyId());
        $user = $company->getUser();

        if ($user) {
            $user->setEmail($company->getEmail());
            $user->setPhone($company->getPhoneStandard());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}