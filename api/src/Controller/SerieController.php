<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Repository\ExerciceSeanceRepository;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SerieController extends AbstractController
{
    // ── POST /api/exercice-seances/{id}/series ────────────────────────────────
    #[Route('/api/exercice-seances/{id}/series', name: 'api_series_create', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        ExerciceSeanceRepository $exSeanceRepo,
        SerieRepository $serieRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user       = $this->getUser();
        $exSeance   = $exSeanceRepo->find($id);

        // Vérifier que l'exercice-séance existe et appartient à l'utilisateur
        if (!$exSeance || $exSeance->getSeance()->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Introuvable'], 404);
        }

        // Vérifier que la séance est en cours
        if ($exSeance->getSeance()->getStatut() !== 'en_cours') {
            return $this->json(['message' => 'La séance n\'est pas en cours'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['nbRepsRealisees']) || !isset($data['poidsKg'])) {
            return $this->json(['message' => 'nbRepsRealisees et poidsKg sont obligatoires'], 400);
        }

        // Calculer le numéro de série automatiquement
        $numSerie = count($exSeance->getSeries()) + 1;

        $serie = new Serie();
        $serie->setExerciceSeance($exSeance);
        $serie->setNumSerie($numSerie);
        $serie->setNbRepsRealisees($data['nbRepsRealisees']);
        $serie->setPoidsKg($data['poidsKg']);
        $serie->setDureeSec($data['dureeSec'] ?? null);
        $serie->setCreatedAt(new \DateTime());

        // ── Calcul du record personnel (PR) ──────────────────────────────────
        // Formule d'Epley : 1RM = poids × (1 + reps / 30)
        $rm1Actuel = $data['poidsKg'] * (1 + $data['nbRepsRealisees'] / 30);

        // Chercher le meilleur 1RM précédent pour cet exercice
        $meilleurPR = $serieRepo->findMaxRM(
            $user,
            $exSeance->getExercice()->getId()
        );

        $estPR = $meilleurPR === null || $rm1Actuel > $meilleurPR;
        $serie->setEstPr($estPR);

        $em->persist($serie);
        $em->flush();

        return $this->json([
            'id'              => $serie->getId(),
            'numSerie'        => $serie->getNumSerie(),
            'nbRepsRealisees' => $serie->getNbRepsRealisees(),
            'poidsKg'         => $serie->getPoidsKg(),
            'estPr'           => $serie->isEstPr(),
            'rm1Estime'       => round($rm1Actuel, 1),
        ], 201);
    }

    // ── GET /api/exercice-seances/{id}/series ─────────────────────────────────
    #[Route('/api/exercice-seances/{id}/series', name: 'api_series_list', methods: ['GET'])]
    public function list(
        int $id,
        ExerciceSeanceRepository $exSeanceRepo
    ): JsonResponse {
        $user     = $this->getUser();
        $exSeance = $exSeanceRepo->find($id);

        if (!$exSeance || $exSeance->getSeance()->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Introuvable'], 404);
        }

        $data = array_map(fn(Serie $s) => [
            'id'              => $s->getId(),
            'numSerie'        => $s->getNumSerie(),
            'nbRepsRealisees' => $s->getNbRepsRealisees(),
            'poidsKg'         => $s->getPoidsKg(),
            'dureeSec'        => $s->getDureeSec(),
            'estPr'           => $s->isEstPr(),
            'createdAt'       => $s->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $exSeance->getSeries()->toArray());

        return $this->json($data);
    }

    // ── DELETE /api/series/{id} ───────────────────────────────────────────────
    #[Route('/api/series/{id}', name: 'api_series_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        SerieRepository $serieRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user  = $this->getUser();
        $serie = $serieRepo->find($id);

        if (!$serie || $serie->getExerciceSeance()->getSeance()->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Introuvable'], 404);
        }

        $em->remove($serie);
        $em->flush();

        return $this->json(['message' => 'Série supprimée'], 200);
    }
}