<?php

namespace App\Controller;

use App\Repository\ExerciceRepository;
use App\Repository\SerieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProgressionController extends AbstractController
{
    // ── GET /api/progression/{exerciceId} ─────────────────────────────────────
    #[Route('/api/progression/{exerciceId}', name: 'api_progression', methods: ['GET'])]
    public function progression(
        int $exerciceId,
        SerieRepository $serieRepo,
        ExerciceRepository $exerciceRepo
    ): JsonResponse {
        $user     = $this->getUser();
        $exercice = $exerciceRepo->find($exerciceId);

        if (!$exercice) {
            return $this->json(['message' => 'Exercice introuvable'], 404);
        }

        $progression = $serieRepo->findProgressionByExercice($user, $exerciceId);

        // Formater les données pour la courbe
        $data = array_map(fn($p) => [
            'date'       => $p['date']->format('Y-m-d'),
            'rm1Estime'  => round((float) $p['rm1Estime'], 1),
            'maxPoids'   => (float) $p['maxPoids'],
            'maxReps'    => (int) $p['maxReps'],
        ], $progression);

        // Calculer le meilleur 1RM global
        $meilleurRM = count($data) > 0
            ? max(array_column($data, 'rm1Estime'))
            : 0;

        return $this->json([
            'exercice'   => [
                'id'  => $exercice->getId(),
                'nom' => $exercice->getNom(),
            ],
            'meilleurRM' => $meilleurRM,
            'progression' => $data,
        ]);
    }

    // ── GET /api/tableau-de-bord ──────────────────────────────────────────────
    #[Route('/api/tableau-de-bord', name: 'api_tableau_de_bord', methods: ['GET'])]
    public function tableauDeBord(
        SerieRepository $serieRepo,
        \App\Repository\SeanceRepository $seanceRepo
    ): JsonResponse {
        $user = $this->getUser();

        // Dernières séances
        $seances = $seanceRepo->findBy(
            ['utilisateur' => $user],
            ['dateDebut' => 'DESC'],
            5
        );

        $dernieresSeances = array_map(fn($s) => [
            'id'       => $s->getId(),
            'nom'      => $s->getNom(),
            'date'     => $s->getDateDebut()?->format('Y-m-d'),
            'dureeMin' => $s->getDureeMin(),
            'statut'   => $s->getStatut(),
        ], $seances);

        // Nombre de séances ce mois-ci
        $debutMois = new \DateTime('first day of this month');
        $seancesMois = $seanceRepo->createQueryBuilder('s')
            ->where('s.utilisateur = :user')
            ->andWhere('s.dateDebut >= :debut')
            ->andWhere('s.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('debut', $debutMois)
            ->setParameter('statut', 'terminee')
            ->getQuery()
            ->getResult();

        // Derniers records personnels
        $prs = $serieRepo->createQueryBuilder('s')
            ->join('s.exerciceSeance', 'es')
            ->join('es.exercice', 'ex')
            ->where('es.seance IN (
                SELECT se FROM App\Entity\Seance se WHERE se.utilisateur = :user
            )')
            ->andWhere('s.estPr = true')
            ->setParameter('user', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $derniersPRs = array_map(fn($pr) => [
            'exercice'   => $pr->getExerciceSeance()->getExercice()->getNom(),
            'poidsKg'    => $pr->getPoidsKg(),
            'reps'       => $pr->getNbRepsRealisees(),
            'rm1Estime'  => round((float) $pr->getPoidsKg() * (1 + $pr->getNbRepsRealisees() / 30), 1),
            'date'       => $pr->getCreatedAt()?->format('Y-m-d'),
        ], $prs);

        return $this->json([
            'seancesMoisEnCours' => count($seancesMois),
            'dernieresSeances'   => $dernieresSeances,
            'derniersPRs'        => $derniersPRs,
        ]);
    }
}