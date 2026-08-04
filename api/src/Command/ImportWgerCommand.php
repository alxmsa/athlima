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
    description: 'Importe les exercices depuis l\'API Wger',
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
        $io->title('Import des exercices depuis Wger');

        // ── Étape 1 : Import des catégories ──────────────────────────────────
        $io->section('1. Import des catégories');
        $categories = $this->wgerService->getCategories();
        $categorieMap = []; // wgerId => entité Categorie

        foreach ($categories as $cat) {
            // Vérifier si la catégorie existe déjà
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
        $muscleMap = []; // wgerId => entité Muscle

        foreach ($muscles as $mus) {
            // Ignorer les muscles sans nom
            $nomMuscle = $mus['name_en'] ?? '';
            if (empty(trim($nomMuscle))) {
                continue;
            }
            $existing = $this->em->getRepository(Muscle::class)
                ->findOneBy(['nom' => $mus['name_en']]);

            if (!$existing) {
                $muscle = new Muscle();
                $muscle->setNom($mus['name_en']);
                $muscle->setGroupeMusculaire($mus['name_en']);
                $muscle->setEstPrincipal(true);
                $this->em->persist($muscle);
                $io->text('✅ Muscle ajouté : ' . $mus['name_en']);
            } else {
                $muscle = $existing;
                $io->text('⏭️  Muscle existant : ' . $mus['name_en']);
            }

            $muscleMap[$mus['id']] = $muscle;
        }

        $this->em->flush();
        $io->success(count($muscles) . ' muscles traités');

        // ── Étape 3 : Import des exercices ───────────────────────────────────
        $io->section('3. Import des exercices');
        $offset = 0;
        $limit  = 20;
        $total  = 0;

        do {
            $exercices = $this->wgerService->getExercices($limit, $offset);

            foreach ($exercices as $ex) {
                // Récupérer le nom depuis les traductions
                $nom = '';
                foreach ($ex['translations'] ?? [] as $translation) {
                    if (in_array($translation['language'], [2, 4])) {
                        $nom = $translation['name'];
                        break;
                    }
                }

                // Ignorer les exercices sans nom
                if (empty(trim($nom))) {
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
                $exercice->setCreatedAt(new \DateTime());
                $exercice->setTypeEffort('force'); 

                // Description depuis les traductions
                foreach ($ex['translations'] ?? [] as $translation) {
                    if (in_array($translation['language'], [2, 4]) && !empty($translation['description'])) {
                        // Nettoyer le HTML de la description
                        $desc = strip_tags($translation['description']);
                        $exercice->setDescription($desc);
                        break;
                    }
                }

                // Associer la catégorie
                if (!empty($ex['category']['id']) && isset($categorieMap[$ex['category']['id']])) {
                    $exercice->setCategorie($categorieMap[$ex['category']['id']]);
                }

                // Associer les muscles principaux
                foreach ($ex['muscles'] ?? [] as $muscleData) {
                    if (isset($muscleMap[$muscleData['id']])) {
                        $exercice->addMuscle($muscleMap[$muscleData['id']]);
                    }
                }

                // Associer les muscles secondaires
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

        $io->success($total . ' exercices importés avec succès !');
        return Command::SUCCESS;
    }
}