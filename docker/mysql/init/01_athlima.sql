-- ═══════════════════════════════════════════
-- ATHLIMA — Script MPD complet — MySQL 8.0
-- ═══════════════════════════════════════════

SET FOREIGN_KEY_CHECKS = 0;

-- ── categorie ───────────────────────────────
CREATE TABLE IF NOT EXISTS categorie (
  id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  nom          VARCHAR(80)    NOT NULL,
  description  TEXT           DEFAULT NULL,
  couleur_hex  VARCHAR(7)     NOT NULL DEFAULT '#888888',
  PRIMARY KEY (id),
  CONSTRAINT uk_categorie_nom UNIQUE KEY (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── muscle ──────────────────────────────────
CREATE TABLE IF NOT EXISTS muscle (
  id                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nom               VARCHAR(80)   NOT NULL,
  groupe_musculaire VARCHAR(80)   NOT NULL,
  est_principal     TINYINT(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  CONSTRAINT uk_muscle_nom UNIQUE KEY (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── utilisateur ─────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateur (
  id             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  email          VARCHAR(180)      NOT NULL,
  mot_de_passe   VARCHAR(255)      NOT NULL,
  roles          JSON              NOT NULL,
  prenom         VARCHAR(80)       NOT NULL,
  nom            VARCHAR(80)       NOT NULL,
  date_naissance DATE              DEFAULT NULL,
  poids_kg       DECIMAL(5,2)      DEFAULT NULL,
  taille_cm      DECIMAL(5,1)      DEFAULT NULL,
  niveau         ENUM('debutant','intermediaire','avance') NOT NULL DEFAULT 'debutant',
  created_at     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT uk_utilisateur_email UNIQUE KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── exercice ────────────────────────────────
CREATE TABLE IF NOT EXISTS exercice (
  id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  categorie_id INT UNSIGNED   NOT NULL,
  nom          VARCHAR(150)   NOT NULL,
  description  TEXT           DEFAULT NULL,
  type_effort  ENUM('force','cardio','souplesse','equilibre') NOT NULL DEFAULT 'force',
  equipement   VARCHAR(100)   DEFAULT NULL,
  image_url    VARCHAR(255)   DEFAULT NULL,
  est_public   TINYINT(1)     NOT NULL DEFAULT 1,
  created_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT uk_exercice_nom   UNIQUE KEY (nom),
  CONSTRAINT fk_exercice_cat   FOREIGN KEY (categorie_id)
    REFERENCES categorie (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── exercice_muscle ─────────────────────────
CREATE TABLE IF NOT EXISTS exercice_muscle (
  exercice_id  INT UNSIGNED  NOT NULL,
  muscle_id    INT UNSIGNED  NOT NULL,
  PRIMARY KEY (exercice_id, muscle_id),
  CONSTRAINT fk_exm_exercice FOREIGN KEY (exercice_id)
    REFERENCES exercice (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_exm_muscle   FOREIGN KEY (muscle_id)
    REFERENCES muscle (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── seance ──────────────────────────────────
CREATE TABLE IF NOT EXISTS seance (
  id             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  utilisateur_id INT UNSIGNED    NOT NULL,
  nom            VARCHAR(150)    NOT NULL,
  date_debut     DATETIME        NOT NULL,
  date_fin       DATETIME        DEFAULT NULL,
  duree_min      SMALLINT UNSIGNED DEFAULT NULL,
  statut         ENUM('en_cours','terminee','annulee') NOT NULL DEFAULT 'en_cours',
  note           TEXT            DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_seance_user FOREIGN KEY (utilisateur_id)
    REFERENCES utilisateur (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── exercice_seance ─────────────────────────
CREATE TABLE IF NOT EXISTS exercice_seance (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  seance_id       INT UNSIGNED     NOT NULL,
  exercice_id     INT UNSIGNED     NOT NULL,
  ordre           TINYINT UNSIGNED NOT NULL,
  nb_series_cible TINYINT UNSIGNED NOT NULL DEFAULT 3,
  nb_reps_cible   TINYINT UNSIGNED NOT NULL DEFAULT 10,
  poids_cible_kg  DECIMAL(6,2)     DEFAULT NULL,
  temps_repos_sec SMALLINT UNSIGNED NOT NULL DEFAULT 90,
  note            TEXT             DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT uk_exs_ordre     UNIQUE KEY (seance_id, ordre),
  CONSTRAINT fk_exs_seance    FOREIGN KEY (seance_id)
    REFERENCES seance (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_exs_exercice  FOREIGN KEY (exercice_id)
    REFERENCES exercice (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── serie ────────────────────────────────────
CREATE TABLE IF NOT EXISTS serie (
  id                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  exercice_seance_id INT UNSIGNED     NOT NULL,
  num_serie          TINYINT UNSIGNED NOT NULL,
  nb_reps_realisees  TINYINT UNSIGNED NOT NULL,
  poids_kg           DECIMAL(6,2)     NOT NULL,
  duree_sec          SMALLINT UNSIGNED DEFAULT NULL,
  est_pr             TINYINT(1)       NOT NULL DEFAULT 0,
  created_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT uk_serie_num  UNIQUE KEY (exercice_seance_id, num_serie),
  CONSTRAINT fk_serie_exs  FOREIGN KEY (exercice_seance_id)
    REFERENCES exercice_seance (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── objectif ─────────────────────────────────
CREATE TABLE IF NOT EXISTS objectif (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  utilisateur_id INT UNSIGNED  NOT NULL,
  type_objectif  ENUM('perte_poids','prise_masse','regularite','performance','endurance') NOT NULL,
  valeur_cible   DECIMAL(7,2)  DEFAULT NULL,
  date_echeance  DATE          DEFAULT NULL,
  est_atteint    TINYINT(1)    NOT NULL DEFAULT 0,
  created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_objectif_user FOREIGN KEY (utilisateur_id)
    REFERENCES utilisateur (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Index de performance ─────────────────────
CREATE INDEX idx_seance_user_date    ON seance           (utilisateur_id, date_debut DESC);
CREATE INDEX idx_seance_statut       ON seance           (statut);
CREATE INDEX idx_exs_seance_ordre    ON exercice_seance  (seance_id, ordre);
CREATE INDEX idx_serie_exs           ON serie            (exercice_seance_id, num_serie);
CREATE INDEX idx_serie_pr            ON serie            (est_pr);
CREATE INDEX idx_exercice_categorie  ON exercice         (categorie_id);
CREATE INDEX idx_muscle_groupe       ON muscle           (groupe_musculaire);

-- ── Seed data ────────────────────────────────
INSERT INTO categorie (nom, description, couleur_hex) VALUES
  ('Compound',    'Exercices multi-articulaires', '#f5a623'),
  ('Isolation',   'Exercices mono-articulaires',  '#4a9eff'),
  ('Cardio',      'Exercices cardiovasculaires',  '#3ddc84'),
  ('Gainage',     'Exercices isométriques',       '#b06aff'),
  ('Souplesse',   'Étirements et mobilité',       '#00d4ff'),
  ('Haltérophilie','Exercices olympiques',         '#ff5c5c');

INSERT INTO muscle (nom, groupe_musculaire, est_principal) VALUES
  ('Pectoraux grand',     'Poitrine',    1),
  ('Pectoraux petit',     'Poitrine',    0),
  ('Grand dorsal',        'Dos',         1),
  ('Trapèzes',            'Dos',         0),
  ('Rhomboïdes',          'Dos',         0),
  ('Lombaires',           'Dos',         0),
  ('Deltoïde antérieur',  'Épaules',     1),
  ('Deltoïde latéral',    'Épaules',     1),
  ('Deltoïde postérieur', 'Épaules',     1),
  ('Biceps',              'Bras',        1),
  ('Triceps',             'Bras',        1),
  ('Avant-bras',          'Bras',        0),
  ('Quadriceps',          'Jambes',      1),
  ('Ischio-jambiers',     'Jambes',      1),
  ('Fessiers',            'Jambes',      1),
  ('Mollets',             'Jambes',      0),
  ('Abdominaux',          'Abdominaux',  1),
  ('Obliques',            'Abdominaux',  0);

INSERT INTO exercice (categorie_id, nom, type_effort, equipement, est_public) VALUES
  (1, 'Développé couché',       'force',    'Barre + banc',  1),
  (1, 'Squat barre',            'force',    'Barre + rack',  1),
  (1, 'Soulevé de terre',       'force',    'Barre',         1),
  (1, 'Développé militaire',    'force',    'Barre',         1),
  (1, 'Rowing barre',           'force',    'Barre',         1),
  (2, 'Curl biceps haltères',   'force',    'Haltères',      1),
  (2, 'Extension triceps',      'force',    'Poulie',        1),
  (3, 'Course à pied',          'cardio',   NULL,            1),
  (4, 'Planche',                'equilibre',NULL,            1);

INSERT INTO exercice_muscle (exercice_id, muscle_id) VALUES
  (1,1),(1,11),(1,7),
  (2,13),(2,14),(2,15),(2,6),
  (3,3),(3,14),(3,15),(3,6),
  (4,7),(4,8),(4,9),(4,11),
  (5,3),(5,4),(5,5),(5,10),
  (6,10),(6,12),
  (7,11),
  (9,17),(9,18),(9,6);

SET FOREIGN_KEY_CHECKS = 1;