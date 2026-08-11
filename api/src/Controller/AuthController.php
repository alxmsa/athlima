<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        // 1. Récupérer les données envoyées en JSON
        $data = json_decode($request->getContent(), true);

        // 2. Vérifier que email et mot de passe sont présents
        if (empty($data['email']) || empty($data['mot_de_passe'])) {
            return $this->json([
                'message' => 'Email et mot de passe obligatoires'
            ], 400);
        }

        // 3. Créer l'entité Utilisateur
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail($data['email']);
        $utilisateur->setPrenom($data['prenom'] ?? '');
        $utilisateur->setNom($data['nom'] ?? '');
        $utilisateur->setRoles(['ROLE_USER']);
        $utilisateur->setNiveau($data['niveau'] ?? 'debutant');

        // 4. Hacher le mot de passe
        $hashedPassword = $passwordHasher->hashPassword(
            $utilisateur,
            $data['mot_de_passe']
        );
        $utilisateur->setMotDePasse($hashedPassword);

        // 5. Valider l'entité
        $errors = $validator->validate($utilisateur);
        if (count($errors) > 0) {
            return $this->json([
                'message' => (string) $errors
            ], 400);
        }

        // 6. Sauvegarder en base
        $em->persist($utilisateur);
        $em->flush();

        // 7. Retourner une réponse 201 Created
        return $this->json([
            'message' => 'Compte créé avec succès',
            'id'      => $utilisateur->getId(),
            'email'   => $utilisateur->getEmail(),
        ], 201);
    }
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        return $this->json([
            'user' => $this->getUser()->getUserIdentifier()
        ]);
    }
}