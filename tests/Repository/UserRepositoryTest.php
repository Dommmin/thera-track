<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repo = self::getContainer()->get(UserRepository::class);
    }

    public function testFindTherapistsReturnsExpectedResults(): void
    {
        $result = $this->repo->findTherapists(null, 'Jan', 'lastName_asc', 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertGreaterThanOrEqual(1, $result['total']);
        $therapists = array_map(fn(User $u) => $u->getSlug(), $result['results']);
        $this->assertContains('jan-kowalski', $therapists);
    }
} 