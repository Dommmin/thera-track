<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\TherapistCacheService;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class TherapistCacheInvalidationSubscriber implements EventSubscriberInterface
{
    public function __construct(private TherapistCacheService $cacheService) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->invalidate($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->invalidate($args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->invalidate($args);
    }

    private function invalidate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof User) {
            return;
        }
        if (!in_array('ROLE_THERAPIST', $entity->getRoles(), true)) {
            return;
        }
        // Inwaliduj cache listy i profilu terapeuty
        $this->cacheService->invalidateTherapistList();
        if ($entity->getSlug()) {
            $this->cacheService->invalidateTherapistProfile($entity->getSlug());
        }
    }
} 