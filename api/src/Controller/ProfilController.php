<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    // ── GET /api/profil ───────────────────────────────────────────────────────
    #[Route('/api/profil', name: 'api_profil_show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'id'            => $user->getId(),
            'email'         => $user->getEmail(),
            'prenom'        => $user->getPrenom(),
            'nom'           => $user->getNom(),
            'dateNaissance' => $user->getDateNaissance()?->format('Y-m-d'),
            'poidsKg'       => $user->getPoidsKg(),
            'tailleCm'      => $user->getTailleCm(),
            'niveau'        => $user->getNiveau(),
        ]);
    }

    // ── PUT /api/profil ───────────────────────────────────────────────────────
    #[Route('/api/profil', name: 'api_profil_update', methods: ['PUT'])]
    public function update(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        // Mettre à jour uniquement les champs fournis
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['poidsKg'])) {
            $user->setPoidsKg($data['poidsKg']);
        }
        if (isset($data['tailleCm'])) {
            $user->setTailleCm($data['tailleCm']);
        }
        if (isset($data['niveau'])) {
            $user->setNiveau($data['niveau']);
        }
        if (isset($data['dateNaissance'])) {
            $user->setDateNaissance(new \DateTime($data['dateNaissance']));
        }

        $em->flush();

        return $this->json([
            'message' => 'Profil mis à jour',
            'prenom'  => $user->getPrenom(),
            'nom'     => $user->getNom(),
            'niveau'  => $user->getNiveau(),
        ]);
    }

    // ── DELETE /api/profil ────────────────────────────────────────────────────
    #[Route('/api/profil', name: 'api_profil_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Compte supprimé'], 200);
    }
}