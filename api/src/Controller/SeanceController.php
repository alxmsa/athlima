<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SeanceController extends AbstractController
{
    // ── GET /api/seances ─────────────────────────────────────────────────────
    #[Route('/api/seances', name: 'api_seances_list', methods: ['GET'])]
    public function index(SeanceRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $seances = $repo->findBy(
            ['utilisateur' => $user],
            ['dateDebut' => 'DESC']
        );

        $data = array_map(fn(Seance $s) => [
            'id'         => $s->getId(),
            'nom'        => $s->getNom(),
            'dateDebut'  => $s->getDateDebut()?->format('Y-m-d H:i:s'),
            'dateFin'    => $s->getDateFin()?->format('Y-m-d H:i:s'),
            'dureeMin'   => $s->getDureeMin(),
            'statut'     => $s->getStatut(),
            'note'       => $s->getNote(),
        ], $seances);

        return $this->json($data);
    }

    // ── POST /api/seances ─────────────────────────────────────────────────────
    #[Route('/api/seances', name: 'api_seances_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SeanceRepository $repo
    ): JsonResponse {
        $user = $this->getUser();

        // Vérifier qu'il n'y a pas déjà une séance en cours
        $seanceEnCours = $repo->findOneBy([
            'utilisateur' => $user,
            'statut'      => 'en_cours'
        ]);

        if ($seanceEnCours) {
            return $this->json([
                'message' => 'Une séance est déjà en cours'
            ], 409);
        }

        $data = json_decode($request->getContent(), true);

        $seance = new Seance();
        $seance->setUtilisateur($user);
        $seance->setNom($data['nom'] ?? 'Séance du ' . date('d/m/Y'));
        $seance->setDateDebut(new \DateTime());
        $seance->setStatut('en_cours');
        $seance->setNote($data['note'] ?? null);

        $em->persist($seance);
        $em->flush();

        return $this->json([
            'id'        => $seance->getId(),
            'nom'       => $seance->getNom(),
            'dateDebut' => $seance->getDateDebut()->format('Y-m-d H:i:s'),
            'statut'    => $seance->getStatut(),
        ], 201);
    }

    // ── GET /api/seances/{id} ─────────────────────────────────────────────────
    #[Route('/api/seances/{id}', name: 'api_seances_show', methods: ['GET'])]
    public function show(int $id, SeanceRepository $repo): JsonResponse
    {
        $user   = $this->getUser();
        $seance = $repo->find($id);

        if (!$seance || $seance->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Séance introuvable'], 404);
        }

        return $this->json([
            'id'        => $seance->getId(),
            'nom'       => $seance->getNom(),
            'dateDebut' => $seance->getDateDebut()?->format('Y-m-d H:i:s'),
            'dateFin'   => $seance->getDateFin()?->format('Y-m-d H:i:s'),
            'dureeMin'  => $seance->getDureeMin(),
            'statut'    => $seance->getStatut(),
            'note'      => $seance->getNote(),
        ]);
    }

    // ── PATCH /api/seances/{id}/terminer ─────────────────────────────────────
    #[Route('/api/seances/{id}/terminer', name: 'api_seances_terminer', methods: ['PATCH'])]
    public function terminer(int $id, SeanceRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user   = $this->getUser();
        $seance = $repo->find($id);

        if (!$seance || $seance->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Séance introuvable'], 404);
        }

        if ($seance->getStatut() !== 'en_cours') {
            return $this->json(['message' => 'La séance n\'est pas en cours'], 400);
        }

        $seance->setDateFin(new \DateTime());
        $seance->setStatut('terminee');

        // Calculer la durée en minutes
        $duree = $seance->getDateDebut()->diff($seance->getDateFin());
        $seance->setDureeMin($duree->h * 60 + $duree->i);

        $em->flush();

        return $this->json([
            'id'       => $seance->getId(),
            'statut'   => $seance->getStatut(),
            'dureeMin' => $seance->getDureeMin(),
            'dateFin'  => $seance->getDateFin()->format('Y-m-d H:i:s'),
        ]);
    }

    // ── DELETE /api/seances/{id} ──────────────────────────────────────────────
    #[Route('/api/seances/{id}', name: 'api_seances_delete', methods: ['DELETE'])]
    public function delete(int $id, SeanceRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user   = $this->getUser();
        $seance = $repo->find($id);

        if (!$seance || $seance->getUtilisateur() !== $user) {
            return $this->json(['message' => 'Séance introuvable'], 404);
        }

        $em->remove($seance);
        $em->flush();

        return $this->json(['message' => 'Séance supprimée'], 200);
    }
}