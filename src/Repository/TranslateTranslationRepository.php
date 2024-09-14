<?php

namespace App\Repository;

use App\Entity\TranslateTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TranslateTranslation>
 *
 * @method TranslateTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranslateTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranslateTranslation[]    findAll()
 * @method TranslateTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranslateTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslateTranslation::class);
    }

    public function findByTranslate(string $name, string $locale)
    {
        return $this->createQueryBuilder('tt')
        ->innerJoin('tt.translate', 't') // Jointure avec l'entité Translate
            ->where('t.name = :name') // Condition sur le champ name de Translate
            ->andWhere('tt.locale = :locale') // Condition sur le champ locale de TranslateTranslation
            ->setParameter('name', $name)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getSingleResult();
    }
    //    /**
    //     * @return TranslateTranslation[] Returns an array of TranslateTranslation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TranslateTranslation
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
