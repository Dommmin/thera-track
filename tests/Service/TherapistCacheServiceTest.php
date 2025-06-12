<?php

namespace App\Tests\Service;

use App\Service\TherapistCacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TherapistCacheServiceTest extends TestCase
{
    public function testGetTherapistListCachesAndInvalidates(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $service = new TherapistCacheService($cache);
        $filters = ['location' => 'Warsaw', 'search' => 'Jan', 'sort' => 'lastName_asc', 'page' => 1, 'perPage' => 6];
        $sortedFilters = $filters;
        ksort($sortedFilters);
        $cacheKey = 'therapist_list_' . md5(json_encode($sortedFilters));
        $expected = ['results' => ['foo'], 'total' => 1];

        $cache->expects($this->once())
            ->method('get')
            ->with($cacheKey, $this->isType('callable'))
            ->willReturn($expected);

        $result = $service->getTherapistList($filters, fn() => $expected);
        $this->assertSame($expected, $result);

        $cache->expects($this->once())
            ->method('invalidateTags')
            ->with(['therapist_list']);
        $service->invalidateTherapistList();
    }

    public function testGetTherapistProfileCachesAndInvalidates(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $service = new TherapistCacheService($cache);
        $slug = 'jan-kowalski';
        $cacheKey = 'therapist_profile_' . $slug;
        $expected = ['foo' => 'bar'];

        $cache->expects($this->once())
            ->method('get')
            ->with($cacheKey, $this->isType('callable'))
            ->willReturn($expected);

        $result = $service->getTherapistProfile($slug, fn() => $expected);
        $this->assertSame($expected, $result);

        $cache->expects($this->once())
            ->method('invalidateTags')
            ->with(['therapist_profile_' . $slug]);
        $service->invalidateTherapistProfile($slug);
    }
} 