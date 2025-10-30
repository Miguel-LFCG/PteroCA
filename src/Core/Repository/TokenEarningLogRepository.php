<?php

namespace App\Core\Repository;

use App\Core\Contract\UserInterface;
use App\Core\Entity\TokenEarningLog;
use App\Core\Enum\TokenEarningMethodEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TokenEarningLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TokenEarningLog::class);
    }

    public function save(TokenEarningLog $tokenEarningLog): void
    {
        $this->getEntityManager()->persist($tokenEarningLog);
        $this->getEntityManager()->flush();
    }

    public function getLastEarningByUserAndMethod(UserInterface $user, TokenEarningMethodEnum $method): ?TokenEarningLog
    {
        return $this->createQueryBuilder('tel')
            ->where('tel.user = :user')
            ->andWhere('tel.method = :method')
            ->setParameter('user', $user)
            ->setParameter('method', $method)
            ->orderBy('tel.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countEarningsInLastHours(UserInterface $user, TokenEarningMethodEnum $method, int $hours): int
    {
        $since = new \DateTime(sprintf('-%d hours', $hours));
        
        return (int) $this->createQueryBuilder('tel')
            ->select('COUNT(tel.id)')
            ->where('tel.user = :user')
            ->andWhere('tel.method = :method')
            ->andWhere('tel.createdAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('method', $method)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
