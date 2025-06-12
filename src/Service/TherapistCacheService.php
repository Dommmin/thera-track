<?php

namespace App\Service;

use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Serwis do cachowania i inwalidacji cache listy i profilu terapeuty z obsługą tagów (Redis)
 */
class TherapistCacheService
{
    public function __construct(
        #[Autowire(service: 'cache.app')] private TagAwareCacheInterface $cache
    )
    {
    }

    public function getTherapistList(array $filters, callable $callback)
    {
        $cacheKey = $this->getListCacheKey($filters);
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(86400); // 1 dzień
            $item->tag(['therapist_list']);
            return $callback();
        });
    }

    public function invalidateTherapistList(): void
    {
        $this->cache->invalidateTags(['therapist_list']);
    }

    public function getTherapistProfile(string $slug, callable $callback)
    {
        $cacheKey = $this->getProfileCacheKey($slug);
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($callback, $slug) {
            $item->expiresAfter(86400); // 1 dzień
            $item->tag(['therapist_profile_' . $slug]);
            return $callback();
        });
    }

    public function invalidateTherapistProfile(string $slug): void
    {
        $this->cache->invalidateTags(['therapist_profile_' . $slug]);
    }

    private function getListCacheKey(array $filters): string
    {
        ksort($filters);
        return 'therapist_list_' . md5(json_encode($filters));
    }

    private function getProfileCacheKey(string $slug): string
    {
        return 'therapist_profile_' . $slug;
    }
} 