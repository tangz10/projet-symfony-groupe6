Prendre le script que j'ai fait avec les sortie qui on plus de 1 mois et qui ne sont pas archivé volontairement!!
Changer le hash des mdp avec celui obtenu avec la commande sur le poste depuis lequel on va présenter!!!

Voir si il y a des messages en attente:
symfony console dbal:run-sql "SELECT COUNT(*) AS pending FROM messenger_messages WHERE queue_name = 'default' AND delivered_at IS NULL"

Supprimer les messsages messenger d'archivage si il y en a:
symfony console dbal:run-sql "DELETE FROM messenger_messages WHERE queue_name = 'default' AND body LIKE '%ArchiveSortiesMessage%'"

Supprimer les messsages messenger d'état si il y en a:
symfony console dbal:run-sql "DELETE FROM messenger_messages WHERE queue_name = 'default' AND body LIKE '%Refresh%StateMessage%'"

lancer le worker avant le serve:
symfony console messenger:consume async --sleep=1 --time-limit=300

regarder les message de messenger:
symfony console dbal:run-sql "SELECT DISTINCT queue_name FROM messenger_messages"

Vérification des changements d'état:
symfony console dbal:run-sql "SELECT s.id, s.nom, e.libelle FROM sortie s LEFT JOIN etat e ON e.id = s.etat_id WHERE s.id BETWEEN 34 AND 42 ORDER BY s.id"
