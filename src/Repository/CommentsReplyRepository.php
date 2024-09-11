<?php

namespace App\Repository;

use App\Entity\CommentsReply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentsReply>
 *
 * @method CommentsReply|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentsReply|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentsReply[]    findAll()
 * @method CommentsReply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentsReplyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentsReply::class);
    }

//    /**
//     * @return CommentsReply[] Returns an array of CommentsReply objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CommentsReply
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
