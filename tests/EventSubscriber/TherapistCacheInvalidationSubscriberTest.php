<?php

namespace App\Tests\EventSubscriber;

use App\Entity\User;
use App\Service\TherapistCacheService;
use App\EventSubscriber\TherapistCacheInvalidationSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class TherapistCacheInvalidationSubscriberTest extends TestCase
{
    public function testInvalidateOnTherapist(): void
    {
        $cache = $this->createMock(TherapistCacheService::class);
        $subscriber = new TherapistCacheInvalidationSubscriber($cache);
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_THERAPIST']);
        $user->method('getSlug')->willReturn('jan-kowalski');
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($user);

        $cache->expects($this->exactly(3))->method('invalidateTherapistList');
        $cache->expects($this->exactly(3))->method('invalidateTherapistProfile')->with('jan-kowalski');

        $subscriber->postPersist($args);
        $subscriber->postUpdate($args);
        $subscriber->postRemove($args);
    }

    public function testNoInvalidateOnNonTherapist(): void
    {
        $cache = $this->createMock(TherapistCacheService::class);
        $subscriber = new TherapistCacheInvalidationSubscriber($cache);
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_PATIENT']);
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($user);

        $cache->expects($this->never())->method('invalidateTherapistList');
        $cache->expects($this->never())->method('invalidateTherapistProfile');

        $subscriber->postPersist($args);
        $subscriber->postUpdate($args);
        $subscriber->postRemove($args);
    }
} 