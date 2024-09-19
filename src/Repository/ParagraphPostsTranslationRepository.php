<?php

namespace App\Repository;

use App\Entity\ParagraphPostsTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParagraphPostsTranslation>
 *
 * @method ParagraphPostsTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParagraphPostsTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParagraphPostsTranslation[]    findAll()
 * @method ParagraphPostsTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParagraphPostsTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParagraphPostsTranslation::class);
    }

//    /**
//     * @return ParagraphPostsTranslation[] Returns an array of ParagraphPostsTranslation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ParagraphPostsTranslation
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
