<?php

namespace App\Repository;

use App\Entity\Translate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Translate>
 *
 * @method Translate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Translate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Translate[]    findAll()
 * @method Translate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranslateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translate::class);
    }
    public function findByTranslate(string $name, string $locale)
    {
        return $this->createQueryBuilder('t')
        ->where('t.name = :name') 
        ->setParameter('name', $name)
        ->leftJoin('t.translateTranslations', 'tt')
        ->addSelect('tt')
        ->andWhere('tt.locale = :locale')  
        ->setParameter('locale', $locale)
        ->getQuery()
        ->getSingleResult();
    }
    //    /**
    //     * @return Translate[] Returns an array of Translate objects
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

    //    public function findOneBySomeField($value): ?Translate
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
