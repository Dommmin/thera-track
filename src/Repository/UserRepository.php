<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function generateUniqueSlug(User $user): void
    {
        $baseSlug = strtolower($user->getFirstName() . '-' . $user->getLastName());
        $slug = $baseSlug;
        $counter = 1;

        while ($this->findOneBy(['slug' => $slug])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $user->setSlug($slug);
    }

    public function findTherapists(?string $location = null, ?string $search = null, ?string $sort = null, int $page = 1, int $perPage = 10): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_THERAPIST"%');

        if ($location) {
            $queryBuilder->andWhere('u.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        if ($search) {
            $queryBuilder->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Sortowanie
        switch ($sort) {
            case 'lastName_desc':
                $queryBuilder->orderBy('u.lastName', 'DESC');
                break;
            case 'price_asc':
                $queryBuilder->orderBy('u.hourlyRate', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('u.hourlyRate', 'DESC');
                break;
            case 'lastName_asc':
            default:
                $queryBuilder->orderBy('u.lastName', 'ASC');
                break;
        }

        $total = (clone $queryBuilder)
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $results = $queryBuilder
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return [
            'results' => $results,
            'total' => $total,
        ];
    }

    public function findAllTherapists(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_THERAPIST%')
            ->getQuery()
            ->getResult();
    }

    public function createUser(User $user): User
    {
        $this->_em->persist($user);
        $this->_em->flush();
        return $user;
    }

    public function updateUserSettings(User $user): void
    {
        $this->_em->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
