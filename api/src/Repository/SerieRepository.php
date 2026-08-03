<?php

namespace App\Repository;

use App\Entity\Serie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Serie>
 */
class SerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Serie::class);
    }

    //    /**
    //     * @return Serie[] Returns an array of Serie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Serie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Trouve le meilleur 1RM estimé pour un utilisateur sur un exercice donné
     * Formule d'Epley : 1RM = poids × (1 + reps / 30)
     */
    public function findMaxRM(mixed $user, int $exerciceId): ?float
    {
        $result = $this->createQueryBuilder('s')
            ->select('MAX(s.poidsKg * (1 + s.nbRepsRealisees / 30.0)) as maxRM')
            ->join('s.exerciceSeance', 'es')
            ->join('es.seance', 'se')
            ->join('es.exercice', 'ex')
            ->where('se.utilisateur = :user')
            ->andWhere('ex.id = :exerciceId')
            ->setParameter('user', $user)
            ->setParameter('exerciceId', $exerciceId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : null;
    }
    /**
     * Retourne l'historique des meilleures performances par séance
     * pour un utilisateur sur un exercice donné
     */
    public function findProgressionByExercice(mixed $user, int $exerciceId): array
    {
        return $this->createQueryBuilder('s')
            ->select(
                'se.dateDebut as date',
                'MAX(s.poidsKg * (1 + s.nbRepsRealisees / 30.0)) as rm1Estime',
                'MAX(s.poidsKg) as maxPoids',
                'MAX(s.nbRepsRealisees) as maxReps'
            )
            ->join('s.exerciceSeance', 'es')
            ->join('es.seance', 'se')
            ->join('es.exercice', 'ex')
            ->where('se.utilisateur = :user')
            ->andWhere('ex.id = :exerciceId')
            ->andWhere('se.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('exerciceId', $exerciceId)
            ->setParameter('statut', 'terminee')
            ->groupBy('se.id')
            ->orderBy('se.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
