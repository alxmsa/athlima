<?php

namespace App\Controller;

use App\Entity\ExerciceSeance;
use App\Repository\ExerciceRepository;
use App\Repository\ExerciceSeanceRepository;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ExerciceSeanceController extends AbstractController
{
    // ── POST /api/seances/{id}/exercices ──────────────────────────────────────
    #[Route('/api/seances/{id}/exercices', name: 'api_seance_add_exercice', methods: ['POST'])]
    public function addExercice(
        int $id,
        Request $request,
        SeanceRepository $seanceRepo,
        ExerciceRepository $exerciceRepo,
        ExerciceSeanceRepository $exSeanceRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user   = $this->getUser();
        $seance = $seanceRepo->find($id);

        // Vérifier que la séance existe et appartient à l'utilisateur
        if (!$seance || $seance->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Séance introuvable'], 404);
        }

        // Vérifier que la séance est en cours
        if ($seance->getStatut() !== 'en_cours') {
            return $this->json(['message' => 'La séance n\'est pas en cours'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['exerciceId'])) {
            return $this->json(['message' => 'exerciceId obligatoire'], 400);
        }

        $exercice = $exerciceRepo->find($data['exerciceId']);
        if (!$exercice) {
            return $this->json(['message' => 'Exercice introuvable'], 404);
        }

        // Calculer l'ordre automatiquement
        $ordre = count($seance->getExerciceSeances()) + 1;

        $exerciceSeance = new ExerciceSeance();
        $exerciceSeance->setSeance($seance);
        $exerciceSeance->setExercice($exercice);
        $exerciceSeance->setOrdre($ordre);
        $exerciceSeance->setNbSeriesCible($data['nbSeriesCible'] ?? 3);
        $exerciceSeance->setNbRepsCible($data['nbRepsCible'] ?? 10);
        $exerciceSeance->setPoidsCibleKg($data['poidsCibleKg'] ?? null);
        $exerciceSeance->setTempsReposSec($data['tempsReposSec'] ?? 90);

        $em->persist($exerciceSeance);
        $em->flush();

        return $this->json([
            'id'            => $exerciceSeance->getId(),
            'ordre'         => $exerciceSeance->getOrdre(),
            'exercice'      => $exercice->getNom(),
            'nbSeriesCible' => $exerciceSeance->getNbSeriesCible(),
            'nbRepsCible'   => $exerciceSeance->getNbRepsCible(),
            'poidsCibleKg'  => $exerciceSeance->getPoidsCibleKg(),
            'tempsReposSec' => $exerciceSeance->getTempsReposSec(),
        ], 201);
    }

    // ── GET /api/seances/{id}/exercices ───────────────────────────────────────
    #[Route('/api/seances/{id}/exercices', name: 'api_seance_list_exercices', methods: ['GET'])]
    public function listExercices(
        int $id,
        SeanceRepository $seanceRepo
    ): JsonResponse {
        $user   = $this->getUser();
        $seance = $seanceRepo->find($id);

        if (!$seance || $seance->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Séance introuvable'], 404);
        }

        $data = [];
        foreach ($seance->getExerciceSeances() as $es) {
            $series = [];
            foreach ($es->getSeries() as $serie) {
                $series[] = [
                    'id'              => $serie->getId(),
                    'numSerie'        => $serie->getNumSerie(),
                    'nbRepsRealisees' => $serie->getNbRepsRealisees(),
                    'poidsKg'         => $serie->getPoidsKg(),
                    'estPr'           => $serie->isEstPr(),
                ];
            }

            $data[] = [
                'id'            => $es->getId(),
                'ordre'         => $es->getOrdre(),
                'exercice'      => [
                    'id'  => $es->getExercice()->getId(),
                    'nom' => $es->getExercice()->getNom(),
                ],
                'nbSeriesCible' => $es->getNbSeriesCible(),
                'nbRepsCible'   => $es->getNbRepsCible(),
                'poidsCibleKg'  => $es->getPoidsCibleKg(),
                'tempsReposSec' => $es->getTempsReposSec(),
                'series'        => $series,
            ];
        }

        return $this->json($data);
    }

    // ── DELETE /api/exercice-seances/{id} ─────────────────────────────────────
    #[Route('/api/exercice-seances/{id}', name: 'api_exercice_seance_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        ExerciceSeanceRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $es   = $repo->find($id);

        if (!$es || $es->getSeance()->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Introuvable'], 404);
        }

        $em->remove($es);
        $em->flush();

        return $this->json(['message' => 'Exercice retiré de la séance'], 200);
    }
}