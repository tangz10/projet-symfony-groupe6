SET FOREIGN_KEY_CHECKS = 0;

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
                                   (4, 'En cours'),
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
                                             (18, 'Les Sables-d\'Olonne', '85100'),
(19, 'Concarneau', '29900'),
(20, 'Blois', '41000');

-- Insertion des participants
-- Hash du mot de passe 'azerty': $2y$13$AIcQSEv7TsGQ6YRqACSQtuCGfDXtcqWjkNQCDyEyIsRNJc/1yte62
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

-- Insertion des sorties (minimum 20 sorties, au moins 2 par état)
INSERT INTO sortie (id, site_id, etat_id, participant_organisateur_id, lieu_id, nom, date_heure_debut, duree, date_limite_inscription, nb_inscriptions_max, infos_sortie, archive) VALUES
-- État 1: Créée (2 sorties)
(1, 1, 1, 1, 1, 'Soirée Bowling', '2024-12-20', 180, '2024-12-18', 12, 'Venez passer une soirée conviviale au bowling', 0),
(2, 2, 1, 3, 3, 'Escape Game Mystère', '2024-12-22', 120, '2024-12-20', 8, 'Résolvons ensemble les énigmes', 0),

-- État 2: Ouverte (3 sorties)
(3, 1, 2, 2, 2, 'Laser Game Challenge', '2025-10-18', 120, '2025-10-16', 16, 'Tournoi de laser game entre collègues', 0),
(4, 3, 2, 4, 4, 'Accrobranche Aventure', '2025-10-20', 240, '2025-10-18', 15, 'Parcours dans les arbres pour tous niveaux', 0),
(5, 4, 2, 7, 5, 'Ciné Détente', '2025-10-22', 150, '2025-10-20', 20, 'Soirée cinéma avec le dernier blockbuster', 0),

-- État 3: Clôturée (3 sorties)
(6, 5, 3, 8, 6, 'Grand Prix Karting', '2025-10-15', 180, '2025-10-13', 10, 'Course de karting avec chronométrage', 0),
(7, 6, 3, 9, 7, 'Patinoire sur Glace', '2025-10-16', 120, '2025-10-14', 25, 'Initiation et perfectionnement patinage', 0),
(8, 7, 3, 10, 8, 'Aqua Fitness', '2025-10-17', 90, '2025-10-15', 12, 'Séance de sport aquatique', 0),

-- État 4: Activité en cours (3 sorties)
(9, 1, 4, 1, 1, 'Tournoi de Bowling', '2025-10-10', 180, '2025-10-08', 16, 'Compétition amicale avec lots à gagner', 0),
(10, 2, 4, 2, 3, 'Escape Game Halloween', '2025-10-10', 120, '2025-10-08', 8, 'Edition spéciale Halloween', 0),
(11, 3, 4, 3, 4, 'Parcours Commando', '2025-10-10', 300, '2025-10-08', 10, 'Dépassez vos limites', 0),

-- État 5: Passée (3 sorties)
(12, 8, 5, 6, 9, 'Golf Initiation', '2024-10-15', 240, '2024-10-13', 8, 'Découverte du golf avec un pro', 0),
(13, 9, 5, 5, 10, 'Battle Paintball', '2024-10-20', 180, '2024-10-18', 20, 'Affrontement par équipes', 0),
(14, 1, 5, 4, 2, 'Laser Game Night', '2024-10-25', 150, '2024-10-23', 14, 'Soirée laser game nocturne', 0),

-- État 6: Annulée (3 sorties)
(15, 2, 6, 7, 3, 'Escape Game Annulé', '2024-11-05', 120, '2024-11-03', 8, 'Annulé pour raisons techniques', 0),
(16, 3, 6, 8, 4, 'Sortie Nature Annulée', '2024-11-07', 360, '2024-11-05', 12, 'Annulé cause météo', 0),
(17, 4, 6, 9, 5, 'Cinéma Reporté', '2024-11-09', 150, '2024-11-07', 15, 'Film non disponible', 0),

-- Sorties supplémentaires
(18, 5, 2, 10, 6, 'Karting Nocturne', '2025-10-28', 180, '2025-10-26', 12, 'Session karting de nuit avec éclairage LED', 0),
(19, 6, 2, 1, 7, 'Gala de Patinage', '2025-10-30', 180, '2025-10-28', 30, 'Spectacle de fin d\'année sur glace', 0),
(20, 7, 1, 2, 8, 'Marathon Aquatique', '2025-11-05', 240, '2025-11-03', 20, 'Défi natation longue distance', 0),
(21, 8, 5, 3, 9, 'Tournoi de Golf', '2024-09-15', 360, '2024-09-13', 16, 'Compétition 18 trous', 0),
(22, 1, 2, 5, 1, 'Bowling de Noël', '2025-12-23', 180, '2025-12-21', 24, 'Spécial fêtes avec animations', 0),

-- Sorties ARCHIVÉES (datant de plus d'un mois - avant le 10/09/2025)
(23, 1, 5, 1, 1, 'Soirée Bowling Rétro', '2024-01-15', 180, '2024-01-13', 12, 'Sortie bowling années 80', 1),
(24, 2, 5, 2, 3, 'Escape Game Hiver', '2024-02-20', 120, '2024-02-18', 10, 'Édition spéciale hiver', 1),
(25, 3, 5, 3, 4, 'Accrobranche Printemps', '2024-03-25', 240, '2024-03-23', 15, 'Parcours dans les arbres au printemps', 1),
(26, 4, 5, 4, 5, 'Festival Cinéma', '2024-04-10', 180, '2024-04-08', 20, 'Festival du film d\'aventure', 1),
(27, 5, 5, 5, 6, 'Grand Prix de Karting', '2024-05-15', 200, '2024-05-13', 12, 'Championnat de karting', 1),
(28, 6, 5, 6, 7, 'Gala de Glace', '2024-06-20', 150, '2024-06-18', 25, 'Spectacle de patinage artistique', 1),
(29, 7, 5, 7, 8, 'Journée Aquatique', '2024-07-25', 240, '2024-07-23', 18, 'Compétition de natation', 1),
(30, 8, 5, 8, 9, 'Tournoi de Golf d\'Été', '2024-08-10', 300, '2024-08-08', 16, 'Tournoi 18 trous', 1),
(31, 9, 5, 9, 10, 'Battle Paintball Été', '2024-08-20', 180, '2024-08-18', 20, 'Affrontement estival', 1),
(32, 10, 5, 10, 2, 'Laser Game Mega Battle', '2024-08-30', 150, '2024-08-28', 16, 'Méga tournoi de laser game', 1);

-- Insertion des inscriptions (sortie_participant)
-- Au minimum 2 inscrits par sortie
INSERT INTO sortie_participant (sortie_id, participant_id) VALUES
-- Sortie 1
(1, 1), (1, 2), (1, 3),
-- Sortie 2
(2, 3), (2, 4), (2, 5),
-- Sortie 3
(3, 2), (3, 6), (3, 7), (3, 8),
-- Sortie 4
(4, 4), (4, 9), (4, 10),
-- Sortie 5
(5, 7), (5, 1), (5, 2), (5, 3), (5, 4),
-- Sortie 6
(6, 8), (6, 5), (6, 6),
-- Sortie 7
(7, 9), (7, 7), (7, 8), (7, 10), (7, 1),
-- Sortie 8
(8, 10), (8, 2), (8, 3),
-- Sortie 9
(9, 1), (9, 4), (9, 5), (9, 6),
-- Sortie 10
(10, 2), (10, 7), (10, 8),
-- Sortie 11
(11, 3), (11, 9), (11, 10),
-- Sortie 12
(12, 6), (12, 1), (12, 2),
-- Sortie 13
(13, 5), (13, 3), (13, 4), (13, 7), (13, 8),
-- Sortie 14
(14, 4), (14, 9), (14, 10),
-- Sortie 15
(15, 7), (15, 1), (15, 2),
-- Sortie 16
(16, 8), (16, 3), (16, 4),
-- Sortie 17
(17, 9), (17, 5), (17, 6),
-- Sortie 18
(18, 10), (18, 7), (18, 8), (18, 1),
-- Sortie 19
(19, 1), (19, 2), (19, 3), (19, 4), (19, 5), (19, 6),
-- Sortie 20
(20, 2), (20, 7), (20, 8),
-- Sortie 21
(21, 3), (21, 9), (21, 10), (21, 1),
-- Sortie 22
(22, 5), (22, 2), (22, 3), (22, 4), (22, 6), (22, 7),

-- Inscriptions pour les sorties archivées
-- Sortie 23
(23, 1), (23, 2), (23, 3),
-- Sortie 24
(24, 2), (24, 4), (24, 5),
-- Sortie 25
(25, 3), (25, 6), (25, 7),
-- Sortie 26
(26, 4), (26, 8), (26, 9),
-- Sortie 27
(27, 5), (27, 1), (27, 2),
-- Sortie 28
(28, 6), (28, 3), (28, 4), (28, 7),
-- Sortie 29
(29, 7), (29, 8), (29, 9),
-- Sortie 30
(30, 8), (30, 10), (30, 1),
-- Sortie 31
(31, 9), (31, 2), (31, 3), (31, 5),
-- Sortie 32
(32, 10), (32, 4), (32, 6), (32, 7);
