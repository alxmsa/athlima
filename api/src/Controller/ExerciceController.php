<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Repository\CategorieRepository;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ExerciceController extends AbstractController
{
    // ── GET /api/exercices ────────────────────────────────────────────────────
    #[Route('/api/exercices', name: 'api_exercices_list', methods: ['GET'])]
    public function index(ExerciceRepository $repo, Request $request): JsonResponse
    {
        $user = $this->getUser();

        // Récupérer les filtres optionnels
        $nom       = $request->query->get('nom');
        $categorie = $request->query->get('categorie');

        // Exercices publics + exercices privés de l'utilisateur
        $qb = $repo->createQueryBuilder('e')
            ->where('e.estPublic = true OR e.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('e.nom', 'ASC');

        if ($nom) {
            $qb->andWhere('e.nom LIKE :nom')
               ->setParameter('nom', '%' . $nom . '%');
        }

        if ($categorie) {
            $qb->andWhere('e.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }

        $exercices = $qb->getQuery()->getResult();

        $data = array_map(fn(Exercice $e) => [
            'id'          => $e->getId(),
            'nom'         => $e->getNom(),
            'description' => $e->getDescription(),
            'typeEffort'  => $e->getTypeEffort(),
            'equipement'  => $e->getEquipement(),
            'estPublic'   => $e->isEstPublic(),
            'categorie'   => $e->getCategorie()?->getNom(),
            'muscles'     => $e->getMuscles()->map(fn($m) => [
                'id'  => $m->getId(),
                'nom' => $m->getNom(),
            ])->toArray(),
        ], $exercices);

        return $this->json($data);
    }

    // ── GET /api/exercices/{id} ───────────────────────────────────────────────
    #[Route('/api/exercices/{id}', name: 'api_exercices_show', methods: ['GET'])]
    public function show(int $id, ExerciceRepository $repo): JsonResponse
    {
        $user     = $this->getUser();
        $exercice = $repo->find($id);

        if (!$exercice) {
            return $this->json(['message' => 'Exercice introuvable'], 404);
        }

        // Vérifier accès : public ou appartient à l'utilisateur
        if (!$exercice->isEstPublic() && $exercice->getCreatedBy() !== $user) {
            return $this->json(['message' => 'Accès refusé'], 403);
        }

        return $this->json([
            'id'          => $exercice->getId(),
            'nom'         => $exercice->getNom(),
            'description' => $exercice->getDescription(),
            'typeEffort'  => $exercice->getTypeEffort(),
            'equipement'  => $exercice->getEquipement(),
            'imageUrl'    => $exercice->getImageUrl(),
            'estPublic'   => $exercice->isEstPublic(),
            'categorie'   => $exercice->getCategorie()?->getNom(),
            'muscles'     => $exercice->getMuscles()->map(fn($m) => [
                'id'             => $m->getId(),
                'nom'            => $m->getNom(),
                'groupeMusculaire' => $m->getGroupeMusculaire(),
            ])->toArray(),
        ]);
    }

    // ── POST /api/exercices ───────────────────────────────────────────────────
    #[Route('/api/exercices', name: 'api_exercices_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CategorieRepository $categorieRepo
    ): JsonResponse {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom'])) {
            return $this->json(['message' => 'Le nom est obligatoire'], 400);
        }

        $exercice = new Exercice();
        $exercice->setNom($data['nom']);
        $exercice->setDescription($data['description'] ?? null);
        $exercice->setTypeEffort($data['typeEffort'] ?? 'force');
        $exercice->setEquipement($data['equipement'] ?? null);
        $exercice->setEstPublic(false); // Exercice privé par défaut
        $exercice->setCreatedBy($user);
        $exercice->setCreatedAt(new \DateTime());

        // Associer une catégorie si fournie
        if (!empty($data['categorieId'])) {
            $categorie = $categorieRepo->find($data['categorieId']);
            if ($categorie) {
                $exercice->setCategorie($categorie);
            }
        }

        $em->persist($exercice);
        $em->flush();

        return $this->json([
            'id'         => $exercice->getId(),
            'nom'        => $exercice->getNom(),
            'typeEffort' => $exercice->getTypeEffort(),
            'estPublic'  => $exercice->isEstPublic(),
        ], 201);
    }

    // ── DELETE /api/exercices/{id} ────────────────────────────────────────────
    #[Route('/api/exercices/{id}', name: 'api_exercices_delete', methods: ['DELETE'])]
    public function delete(int $id, ExerciceRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user     = $this->getUser();
        $exercice = $repo->find($id);

        if (!$exercice) {
            return $this->json(['message' => 'Exercice introuvable'], 404);
        }

        // Seul le créateur peut supprimer son exercice
        if ($exercice->getCreatedBy() !== $user) {
            return $this->json(['message' => 'Vous ne pouvez pas supprimer cet exercice'], 403);
        }

        $em->remove($exercice);
        $em->flush();

        return $this->json(['message' => 'Exercice supprimé'], 200);
    }
}