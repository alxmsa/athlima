<?php

namespace App\Command;

use App\Entity\Categorie;
use App\Entity\Exercice;
use App\Entity\Muscle;
use App\Service\WgerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-wger',
    description: 'Importe les exercices depuis l\'API Wger en français uniquement',
)]
class ImportWgerCommand extends Command
{
    public function __construct(
        private WgerService $wgerService,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des exercices depuis Wger — Français uniquement');

        // ── Étape 1 : Import des catégories ──────────────────────────────────
        $io->section('1. Import des catégories');
        $categories = $this->wgerService->getCategories();
        $categorieMap = [];

        foreach ($categories as $cat) {
            $existing = $this->em->getRepository(Categorie::class)
                ->findOneBy(['nom' => $cat['name']]);

            if (!$existing) {
                $categorie = new Categorie();
                $categorie->setNom($cat['name']);
                $categorie->setDescription('Importé depuis Wger');
                $categorie->setCouleurHex('#3B82F6');
                $this->em->persist($categorie);
                $io->text('✅ Catégorie ajoutée : ' . $cat['name']);
            } else {
                $categorie = $existing;
                $io->text('⏭️  Catégorie existante : ' . $cat['name']);
            }

            $categorieMap[$cat['id']] = $categorie;
        }

        $this->em->flush();
        $io->success(count($categories) . ' catégories traitées');

        // ── Étape 2 : Import des muscles ─────────────────────────────────────
        $io->section('2. Import des muscles');
        $muscles = $this->wgerService->getMuscles();
        $muscleMap = [];

        foreach ($muscles as $mus) {
            $nomMuscle = $mus['name_en'] ?? '';
            if (empty(trim($nomMuscle))) {
                continue;
            }

            $existing = $this->em->getRepository(Muscle::class)
                ->findOneBy(['nom' => $nomMuscle]);

            if (!$existing) {
                $muscle = new Muscle();
                $muscle->setNom($nomMuscle);
                $muscle->setGroupeMusculaire($nomMuscle);
                $muscle->setEstPrincipal(true);
                $this->em->persist($muscle);
                $io->text('✅ Muscle ajouté : ' . $nomMuscle);
            } else {
                $muscle = $existing;
                $io->text('⏭️  Muscle existant : ' . $nomMuscle);
            }

            $muscleMap[$mus['id']] = $muscle;
        }

        $this->em->flush();
        $io->success(count($muscles) . ' muscles traités');

        // ── Étape 3 : Import des exercices EN FRANÇAIS UNIQUEMENT ─────────────
        $io->section('3. Import des exercices français');
        $offset = 0;
        $limit  = 100;
        $total  = 0;
        $ignores = 0;

        do {
            $exercices = $this->wgerService->getExercices($limit, $offset);

            foreach ($exercices as $ex) {
                // ── Chercher UNIQUEMENT la traduction française (language = 12) ──
                $nom = '';
                $description = '';

                foreach ($ex['translations'] ?? [] as $translation) {
                    if ($translation['language'] === 12) {
                        $nom = trim($translation['name']);
                        $description = strip_tags($translation['description'] ?? '');
                        break;
                    }
                }

                // Ignorer si pas de traduction française
                if (empty($nom)) {
                    $ignores++;
                    continue;
                }

                // Vérifier si l'exercice existe déjà
                $existing = $this->em->getRepository(Exercice::class)
                    ->findOneBy(['nom' => $nom]);

                if ($existing) {
                    $io->text('⏭️  Exercice existant : ' . $nom);
                    continue;
                }

                $exercice = new Exercice();
                $exercice->setNom($nom);
                $exercice->setEstPublic(true);
                $exercice->setTypeEffort('force');
                $exercice->setCreatedAt(new \DateTime());

                // Description en français
                if (!empty($description)) {
                    $exercice->setDescription($description);
                }

                // Catégorie
                if (!empty($ex['category']['id']) && isset($categorieMap[$ex['category']['id']])) {
                    $exercice->setCategorie($categorieMap[$ex['category']['id']]);
                }

                // Muscles principaux
                foreach ($ex['muscles'] ?? [] as $muscleData) {
                    if (isset($muscleMap[$muscleData['id']])) {
                        $exercice->addMuscle($muscleMap[$muscleData['id']]);
                    }
                }

                // Muscles secondaires
                foreach ($ex['muscles_secondary'] ?? [] as $muscleData) {
                    if (isset($muscleMap[$muscleData['id']])) {
                        $exercice->addMuscle($muscleMap[$muscleData['id']]);
                    }
                }

                $this->em->persist($exercice);
                $total++;
                $io->text('✅ Exercice ajouté : ' . $nom);
            }

            $this->em->flush();
            $offset += $limit;

        } while (count($exercices) === $limit);

        $io->success($total . ' exercices français importés ! (' . $ignores . ' ignorés — pas de traduction française)');
        return Command::SUCCESS;
    }
}