# Journal de bord — Athlima

Application mobile de suivi sportif · Formation CDA · Moussa Diop

---

## Jalon 1 — Cahier des charges (Janvier 2026)

**Semaine 1**
- Définition du concept et des problématiques utilisateurs
- Identification des cibles (sportifs amateurs et confirmés)
- Rédaction des fonctionnalités utilisateur et administrateur

**Semaine 2**
- Rédaction des exigences techniques (stack, architecture)
- Justification des choix technologiques (Symfony vs Laravel, React Native)
- Ajout des exigences non fonctionnelles (performances, compatibilité, sécurité)

**Semaine 3**
- Correction suite retour formateur :
  - Ajout du commanditaire (startup Athlima)
  - Détail des fonctionnalités vagues (PR, tableau de bord, progression)
  - Ajout glossaire 14 termes (API REST, JWT, ORM, RGPD...)
  - Correction risque API externe (Wger comme fallback)

---

## Jalon 2 — UI/UX (Février 2026)

**Semaine 1**
- Définition de la charte graphique (couleurs, typographie)
- Création des wireframes basse fidélité (écrans principaux)

**Semaine 2**
- Maquettes haute fidélité sur Figma
- Parcours utilisateur : inscription → séance → progression

**Semaine 3**
- Retours et ajustements des maquettes
- Validation du design system

---

## Jalon 3 — Modélisation BDD & Infrastructure (Mars 2026)

**Semaine 1**
- Analyse du domaine métier et identification des entités
- Rédaction du dictionnaire des données (52 attributs, 8 entités)
- Création du MCD MERISE sur Mocodo.net

**Semaine 2**
- Passage MCD → MLD sur dbdiagram.io
- Création du MPD (script SQL complet, 8 tables, 9 index)
- Mise en place de l'environnement Docker (4 services)

**Semaine 3**
- Création des 8 entités Doctrine avec make:entity
- Génération et application des migrations
- Validation doctrine:schema:validate ✅
- Push sur GitHub

---

## Jalon 4 — Conception technique (Avril 2026)

**Semaine 1**
- Création du diagramme de cas d'utilisation UML (draw.io)
- 2 acteurs, 20 UC, relations include/extend

**Semaine 2**
- Diagramme de séquence : Authentification JWT
- Diagramme de classes UML complet (Entity, Controller, Service, Repository)

**Semaine 3**
- Description de l'architecture (MVC, N-tiers, SOLID)
- Documentation des bundles et composants externes
- Rédaction du PDF de conception

---

## Jalon 5 — Développement API (Mai - Juillet 2026)

**Semaine 1 — Authentification**
- Installation LexikJWTAuthenticationBundle
- Génération des clés RSA
- Configuration security.yaml (firewalls, access_control)
- Endpoint POST /api/register ✅
- Endpoint POST /api/login_check ✅
- Endpoint GET /api/me ✅

**Semaine 2 — Séances**
- Endpoint GET/POST /api/seances ✅
- Endpoint GET /api/seances/{id} ✅
- Endpoint PATCH /api/seances/{id}/terminer ✅
- Endpoint DELETE /api/seances/{id} ✅

**Semaine 3 — Exercices & Wger**
- Installation symfony/http-client
- Création WgerService (appels API externe)
- Création commande app:import-wger
- Import 824 exercices depuis Wger ✅
- Endpoint GET/POST/DELETE /api/exercices ✅

**Semaine 4 — Séries & Progression**
- Endpoint POST/GET /api/seances/{id}/exercices ✅
- Endpoint POST/GET /api/exercice-seances/{id}/series ✅
- Calcul automatique du record personnel (formule Epley) ✅
- Endpoint GET /api/progression/{exerciceId} ✅
- Endpoint GET /api/tableau-de-bord ✅
- Endpoint GET/PUT/DELETE /api/profil ✅

**Semaine 5 — Tests & CI/CD**
- Installation PHPUnit
- Tests unitaires SerieTest (5 tests) ✅
- Tests fonctionnels AuthControllerTest (4 tests) ✅
- Configuration GitHub Actions (ci.yml) ✅
- Pipeline CI opérationnel ✅

---

## Jalon 6 — Frontend React Native (Juin - Juillet 2026)

🔄 En cours...