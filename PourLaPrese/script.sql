SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM note;
DELETE FROM sortie_participant;
DELETE FROM sortie;
DELETE FROM participant;
DELETE FROM lieu;
DELETE FROM ville;
DELETE FROM site;
DELETE FROM etat;

SET FOREIGN_KEY_CHECKS = 1;

-- Insertion des états
INSERT INTO etat (id, libelle) VALUES
                                   (1, 'Créée'),
                                   (2, 'Ouverte'),
                                   (3, 'Clôturée'),
                                   (4, 'Activité en cours'),
                                   (5, 'Passée'),
                                   (6, 'Annulée');

-- Insertion des sites
INSERT INTO site (id, nom) VALUES
                               (1, 'ENI Nantes'),
                               (2, 'ENI Rennes'),
                               (3, 'ENI Niort'),
                               (4, 'ENI Quimper'),
                               (5, 'ENI Angers'),
                               (6, 'ENI Le Mans'),
                               (7, 'ENI Tours'),
                               (8, 'ENI Vannes'),
                               (9, 'ENI La Roche-sur-Yon'),
                               (10, 'ENI Brest');

-- Insertion des villes
INSERT INTO ville (id, nom, code_postal) VALUES
                                             (1, 'Nantes', '44000'),
                                             (2, 'Rennes', '35000'),
                                             (3, 'Niort', '79000'),
                                             (4, 'Quimper', '29000'),
                                             (5, 'Angers', '49000'),
                                             (6, 'Le Mans', '72000'),
                                             (7, 'Tours', '37000'),
                                             (8, 'Vannes', '56000'),
                                             (9, 'La Roche-sur-Yon', '85000'),
                                             (10, 'Brest', '29200'),
                                             (11, 'Saint-Herblain', '44800'),
                                             (12, 'Cesson-Sévigné', '35510'),
                                             (13, 'Cholet', '49300'),
                                             (14, 'Lorient', '56100'),
                                             (15, 'Saint-Nazaire', '44600'),
                                             (16, 'Laval', '53000'),
                                             (17, 'Saumur', '49400'),
                                             (18, 'Les Sables-d''Olonne', '85100'),
                                             (19, 'Concarneau', '29900'),
                                             (20, 'Blois', '41000');

-- Insertion des participants
-- Hash du mot de passe remplacé : $2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62
INSERT INTO participant (id, site_id, email, roles, password, nom, prenom, telephone, administrateur, actif) VALUES
                                                                                                                 (1, 1, 'mathis.delahais2024@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Delahais', 'Mathis', '0612345678', 0, 1),
                                                                                                                 (2, 1, 'johann.degennes2024@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Degennes', 'Johann', '0623456789', 0, 1),
                                                                                                                 (3, 2, 'landrygabriel960@gmail.com', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Landry', 'Gabriel', '0634567890', 0, 1),
                                                                                                                 (4, 3, 'sophie.martin@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Martin', 'Sophie', '0645678901', 0, 1),
                                                                                                                 (5, 1, 'lucas.bernard@campus-eni.fr', '["ROLE_ADMIN"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Bernard', 'Lucas', '0656789012', 1, 1),
                                                                                                                 (6, 2, 'emma.dubois@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Dubois', 'Emma', '0667890123', 0, 1),
                                                                                                                 (7, 4, 'thomas.leroy@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Leroy', 'Thomas', '0678901234', 0, 1),
                                                                                                                 (8, 5, 'julie.moreau@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Moreau', 'Julie', '0689012345', 0, 1),
                                                                                                                 (9, 6, 'alexandre.petit@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Petit', 'Alexandre', '0690123456', 0, 1),
                                                                                                                 (10, 7, 'marie.garcia@campus-eni.fr', '["ROLE_USER"]', '$2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62', 'Garcia', 'Marie', '0601234567', 0, 1);

-- Insertion des lieux
INSERT INTO lieu (id, ville_id, nom, rue, latitude, longitude) VALUES
                                                                   (1, 1, 'Bowling de Nantes', '15 Rue de la Jonelière', 47.2808, -1.5206),
                                                                   (2, 1, 'Laser Game Evolution', '2 Rue du Chêne Lassé', 47.2452, -1.5729),
                                                                   (3, 2, 'Escape Game Rennes', '24 Rue de la Monnaie', 48.1134, -1.6833),
                                                                   (4, 3, 'Accrobranche Niort', 'Parc de la Venise Verte', 46.3200, -0.4600),
                                                                   (5, 4, 'Cinéma Quimper', '2 Boulevard Dupleix', 47.9960, -4.0974),
                                                                   (6, 5, 'Karting Angers', 'Route de Beaufort', 47.4784, -0.5632),
                                                                   (7, 6, 'Patinoire Le Mans', '54 Avenue Yzeux', 47.9948, 0.1885),
                                                                   (8, 7, 'Piscine du Lac Tours', '277 Rue de Grammont', 47.3831, 0.6927),
                                                                   (9, 8, 'Golf de Vannes', 'Route du Pouldour', 47.6488, -2.7760),
                                                                   (10, 9, 'Paintball La Roche', 'Zone de Beaupuy', 46.6700, -1.4300);

-- Insertion des sorties (avec colonne archivee)
INSERT INTO sortie (id, site_id, etat_id, participant_organisateur_id, lieu_id, nom, date_heure_debut, duree, date_limite_inscription, nb_inscriptions_max, infos_sortie, archivee) VALUES
                                                                                                                                                                                        -- État 1: Créée (non archivées)
                                                                                                                                                                                        (1, 1, 1, 1, 1, 'Soirée Bowling', '2025-11-20 18:00:00', 180, '2025-11-18 23:59:59', 12, 'Venez passer une soirée conviviale au bowling', 0),
                                                                                                                                                                                        (2, 2, 1, 3, 3, 'Escape Game Mystère', '2025-11-22 14:00:00', 120, '2025-11-20 23:59:59', 8, 'Résolvons ensemble les énigmes', 0),

                                                                                                                                                                                        -- État 2: Ouverte (non archivées)
                                                                                                                                                                                        (3, 1, 2, 2, 2, 'Laser Game Challenge', '2025-10-25 19:00:00', 120, '2025-10-23 23:59:59', 16, 'Tournoi de laser game entre collègues', 0),
                                                                                                                                                                                        (4, 3, 2, 4, 4, 'Accrobranche Aventure', '2025-10-28 10:00:00', 240, '2025-10-26 23:59:59', 15, 'Parcours dans les arbres pour tous niveaux', 0),
                                                                                                                                                                                        (5, 4, 2, 7, 5, 'Ciné Détente', '2025-10-30 20:30:00', 150, '2025-10-28 23:59:59', 20, 'Soirée cinéma avec le dernier blockbuster', 0),

                                                                                                                                                                                        -- État 3: Clôturée (non archivées)
                                                                                                                                                                                        (6, 5, 3, 8, 6, 'Grand Prix Karting', '2025-10-16 14:00:00', 180, '2025-10-12 23:59:59', 10, 'Course de karting avec chronométrage', 0),
                                                                                                                                                                                        (7, 6, 3, 9, 7, 'Patinoire sur Glace', '2025-10-17 15:00:00', 120, '2025-10-12 23:59:59', 25, 'Initiation et perfectionnement patinage', 0),
                                                                                                                                                                                        (8, 7, 3, 10, 8, 'Aqua Fitness', '2025-10-18 18:30:00', 90, '2025-10-12 23:59:59', 12, 'Séance de sport aquatique', 0),

                                                                                                                                                                                        -- État 4: Activité en cours (non archivées)
                                                                                                                                                                                        (9, 1, 4, 1, 1, 'Tournoi de Bowling', '2025-10-13 09:00:00', 180, '2025-10-11 23:59:59', 16, 'Compétition amicale avec lots à gagner', 0),
                                                                                                                                                                                        (10, 2, 4, 2, 3, 'Escape Game Halloween', '2025-10-13 14:00:00', 120, '2025-10-11 23:59:59', 8, 'Edition spéciale Halloween', 0),
                                                                                                                                                                                        (11, 3, 4, 3, 4, 'Parcours Commando', '2025-10-13 10:30:00', 300, '2025-10-11 23:59:59', 10, 'Dépassez vos limites', 0),

                                                                                                                                                                                        -- État 5: Passée récente (non archivées, < 1 mois)
                                                                                                                                                                                        (12, 8, 5, 6, 9, 'Golf Initiation', '2025-09-15 09:00:00', 240, '2025-09-13 23:59:59', 8, 'Découverte du golf avec un pro', 0),
                                                                                                                                                                                        (13, 9, 5, 5, 10, 'Battle Paintball', '2025-09-20 13:00:00', 180, '2025-09-18 23:59:59', 20, 'Affrontement par équipes', 0),
                                                                                                                                                                                        (14, 1, 5, 4, 2, 'Laser Game Night', '2025-10-05 20:00:00', 150, '2025-10-03 23:59:59', 14, 'Soirée laser game nocturne', 0),

                                                                                                                                                                                        -- État 6: Annulée (non archivées)
                                                                                                                                                                                        (15, 2, 6, 7, 3, 'Escape Game Annulé', '2025-10-20 16:00:00', 120, '2025-10-18 23:59:59', 8, 'Annulé pour raisons techniques', 0),
                                                                                                                                                                                        (16, 3, 6, 8, 4, 'Sortie Nature Annulée', '2025-10-22 10:00:00', 360, '2025-10-20 23:59:59', 12, 'Annulé cause météo', 0),
                                                                                                                                                                                        (17, 4, 6, 9, 5, 'Cinéma Reporté', '2025-10-24 21:00:00', 150, '2025-10-22 23:59:59', 15, 'Film non disponible', 0),

                                                                                                                                                                                        -- Sorties supplémentaires
                                                                                                                                                                                        (18, 5, 2, 10, 6, 'Karting Nocturne', '2025-11-08 20:00:00', 180, '2025-11-06 23:59:59', 12, 'Session karting de nuit avec éclairage LED', 0),
                                                                                                                                                                                        (19, 6, 2, 1, 7, 'Gala de Patinage', '2025-11-15 19:00:00', 180, '2025-11-13 23:59:59', 30, 'Spectacle de fin d''année sur glace', 0),
                                                                                                                                                                                        (20, 7, 1, 2, 8, 'Marathon Aquatique', '2025-12-05 08:00:00', 240, '2025-12-03 23:59:59', 20, 'Défi natation longue distance', 0),

                                                                                                                                                                                        -- État 5: Passée ancienne (ARCHIVÉES, > 1 mois)
                                                                                                                                                                                        (21, 8, 5, 3, 9, 'Tournoi de Golf', '2025-08-15 09:00:00', 360, '2025-08-13 23:59:59', 16, 'Compétition 18 trous', 1),
                                                                                                                                                                                        (22, 1, 3, 5, 1, 'Bowling Halloween', '2025-10-31 18:00:00', 180, '2025-10-12 23:59:59', 24, 'Spécial Halloween avec animations', 0);

-- Sorties supplémentaires à archiver automatiquement (> 1 mois dans le passé)
INSERT INTO sortie (id, site_id, etat_id, participant_organisateur_id, lieu_id, nom, date_heure_debut, duree, date_limite_inscription, nb_inscriptions_max, infos_sortie, archivee) VALUES
                                                                                                                                                                                        (23, 2, 5, 2, 2, 'Randonnée Estivale', '2025-08-10 09:00:00', 240, '2025-08-08 23:59:59', 20, 'Balade en nature autour de Rennes', 0),
                                                                                                                                                                                        (24, 3, 5, 4, 4, 'Pique-nique au Parc', '2025-07-20 12:00:00', 180, '2025-07-18 23:59:59', 25, 'Pique-nique convivial au parc de Niort', 0),
                                                                                                                                                                                        (25, 1, 5, 1, 1, 'Afterwork de Juin', '2025-06-28 18:30:00', 120, '2025-06-26 23:59:59', 30, 'Afterwork détente au bowling', 0),
                                                                                                                                                                                        (26, 5, 6, 6, 5, 'Sortie Annulée - Orage', '2025-08-01 15:00:00', 120, '2025-07-30 23:59:59', 12, 'Sortie annulée en raison des conditions météo', 0),
                                                                                                                                                                                        (27, 7, 5, 7, 7, 'Tournoi d''été', '2025-07-05 10:00:00', 300, '2025-07-01 23:59:59', 25, 'Tournoi amical de l''été', 0),
                                                                                                                                                                                        (28, 9, 5, 9, 9, 'Visite du Musée', '2025-08-22 14:00:00', 90, '2025-08-20 23:59:59', 15, 'Découverte des expositions temporaires', 0);

-- **Sorties anciennes (> 1 mois) ajoutées pour tests d'archivage (NE PAS les marquer archivées)**
INSERT INTO sortie (id, site_id, etat_id, participant_organisateur_id, lieu_id, nom, date_heure_debut, duree, date_limite_inscription, nb_inscriptions_max, infos_sortie, archivee) VALUES
                                                                                                                                                                                        (29, 4, 5, 4, 4, 'Randonnée Bord de Rance', '2025-08-01 09:00:00', 300, '2025-07-30 23:59:59', 20, 'Randonnée longue le long de la Rance', 0),
                                                                                                                                                                                        (30, 1, 5, 1, 1, 'Afterwork d''été - Spécial', '2025-06-15 18:00:00', 150, '2025-06-13 23:59:59', 30, 'Afterwork convivial avec musique', 0),
                                                                                                                                                                                        (31, 2, 5, 2, 3, 'Matinée Course d''Orientation', '2025-05-20 10:00:00', 180, '2025-05-18 23:59:59', 25, 'Course d''orientation en équipes', 0),
                                                                                                                                                                                        (32, 5, 5, 6, 5, 'Atelier Photo Urbain', '2025-04-10 14:00:00', 240, '2025-04-08 23:59:59', 15, 'Atelier découverte photo en ville', 0),
                                                                                                                                                                                        (33, 7, 5, 7, 7, 'Tournoi Pétanque Estival', '2025-07-03 20:00:00', 180, '2025-07-01 23:59:59', 16, 'Pétanque en équipe suivi d''un apéro', 0);

-- =========================================
-- Bloc de sorties TEST pour valider le worker d'états (15/10/2025 ~11:10)
-- Noms explicites → état attendu après passage du worker
-- =========================================
INSERT INTO sortie (id, site_id, etat_id, participant_organisateur_id, lieu_id, nom, date_heure_debut, duree, date_limite_inscription, nb_inscriptions_max, infos_sortie, archivee) VALUES
    -- Doit devenir: Ouverte (avant début et avant date limite, non pleine)
    (34, 1, 1, 1, 1, 'TEST_Ouverte',       '2025-10-18', 180, '2025-10-17', 10, 'Test: devrait passer à Ouverte', 0),
    (35, 2, 1, 2, 2, 'TEST_Ouverte1',      '2025-10-19', 120, '2025-10-18', 15, 'Test: devrait passer à Ouverte', 0),

    -- Doit devenir: Clôturée (date limite dépassée)
    (36, 3, 2, 3, 3, 'TEST_Cloturee',      '2025-10-20', 120, '2025-10-10', 15, 'Test: devrait passer à Clôturée (date limite dépassée)', 0),
    -- Doit devenir: Clôturée (capacité pleine)
    (37, 4, 2, 4, 4, 'TEST_Cloturee1',     '2025-10-21', 120, '2025-10-20', 2,  'Test: devrait passer à Clôturée (plein)', 0),

    -- Doit devenir: Activité en cours (début aujourd'hui, durée couvre la journée)
    (38, 5, 2, 5, 5, 'TEST_EnCours',       '2025-10-15', 1440, '2025-10-14', 20, 'Test: devrait passer à Activité en cours', 0),
    (39, 6, 2, 6, 6, 'TEST_EnCours1',      '2025-10-15', 1440, '2025-10-14', 30, 'Test: devrait passer à Activité en cours', 0),

    -- Doit devenir: Passée (début dans le passé)
    (40, 7, 2, 7, 7, 'TEST_Passee',        '2025-10-10', 60,  '2025-10-08', 20, 'Test: devrait passer à Passée', 0),
    (41, 8, 2, 8, 8, 'TEST_Passee1',       '2025-09-30', 120, '2025-09-28', 25, 'Test: devrait passer à Passée', 0),

    -- Doit rester: Annulée (le worker ne touche pas aux sorties annulées)
    (42, 9, 6, 9, 9, 'TEST_Annulee',       '2025-10-18', 120, '2025-10-16', 15, 'Test: reste Annulée', 0);

-- Remplissage pour capacité pleine de TEST_Cloturee1 (nb_max = 2)
INSERT INTO sortie_participant (sortie_id, participant_id) VALUES
    (37, 1), (37, 2);

-- =========================================
-- Fin du bloc de tests worker d’états
-- =========================================
