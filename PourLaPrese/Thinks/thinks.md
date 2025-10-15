# Gestion automatique des états des sorties

Ce document explique l’architecture, les règles métiers, les composants ajoutés, la configuration et la façon de tester/présenter la mise à jour automatique des états d’une sortie (Créée → Ouverte → Clôturée → Activité en cours → Passée, avec Annulée en parallèle) basée sur Symfony Messenger, avec un fallback sans cron ni planificateur Windows.

---

## Règles métiers (source de vérité)

- Créée → Ouverte: dès la “publication”.
- Ouverte: par défaut tant que:
  - dateLimiteInscription n’est pas dépassée
  - nbInscrits < nbInscriptionsMax
- Clôturée: si l’un des deux est vrai:
  - now > dateLimiteInscription
  - nbInscrits ≥ nbInscriptionsMax
- Activité en cours: quand now ∈ [dateHeureDebut, dateHeureDebut + durée]
- Passée: quand now ≥ (dateHeureDebut + durée)
- Annulée: mise manuelle par l’organisateur (ou admin), pas de transition auto ensuite.

Notes:
- Les libellés d’états sont traités de manière insensible à la casse (“Ouverte” ≃ “ouverte”).
- La colonne `date_heure_debut` est de type DATE dans notre mapping: on considère l’heure à 00:00 par défaut; la durée reste appliquée (en minutes) sur ce repère.

---

## Architecture

Deux mécanismes complémentaires et idempotents:

1) Réaction immédiate (évènements métier → messages ciblés)
   - Après une action utilisateur (publier, annuler, s’inscrire, se désister), on envoie `RefreshOneSortieStateMessage(sortieId)` → un handler recalcule l’état de cette sortie seulement.

2) Batch périodique (toutes X minutes)
   - Un scheduler (listener HTTP) planifie un tick `RefreshSortiesStateMessage` toutes `state_refresh_interval_minutes` via Messenger (worker).
   - Fallback sans worker: le même listener exécute, au plus une fois toutes `state_refresh_interval_minutes`, un rafraîchissement batch côté serveur web (au premier hit HTTP) afin d’éviter toute dépendance à un cron.

Le calcul métier est centralisé dans un service pur (résolveur), ce qui rend le code testable et maintenable.

---

## Composants ajoutés

Services (src/Service)
- `SortieStateResolver.php`: calcule l’état attendu d’une sortie et l’applique si besoin.
- `SortieStateRefresher.php`: rafraîchit en lot les états des sorties non archivées.

Messages (src/Message)
- `RefreshOneSortieStateMessage.php`: message ciblant une sortie par id.
- `RefreshSortiesStateMessage.php`: message “tick” pour un batch.

Handlers (src/MessageHandler)
- `RefreshOneSortieStateMessageHandler.php`: applique le résolveur sur une sortie.
- `RefreshSortiesStateMessageHandler.php`: lance le batch via le refresher.

Scheduler (src/EventListener)
- `StatusRefreshSchedulerListener.php`: planifie le tick toutes X minutes via Messenger ET déclenche un fallback local (cache) sans worker.

Intégration contrôleur (src/Controller)
- `SortieController.php`: envoi d’un `RefreshOneSortieStateMessage` après publier/annuler/inscrire/désister.

Configuration
- `config/packages/messenger.yaml`: routing des nouveaux messages vers `async`.
- `config/services.yaml`: paramètres et injection du scheduler (intervalle).

---

## Configuration

Fichier: `config/services.yaml`

```yaml
parameters:
  # Intervalle (minutes) de rafraîchissement des états (batch + fallback)
  state_refresh_interval_minutes: 5
```

Fichier: `config/packages/messenger.yaml`

```yaml
default_bus: messenger.bus.default
routing:
  App\Message\RefreshOneSortieStateMessage: async
  App\Message\RefreshSortiesStateMessage: async
```

Transport Messenger: `async` (Doctrine) est déjà configuré dans le projet.

---

## Utilisation (démo et exploitation)

1) Worker (recommandé)

Lancer un worker qui consomme `async` (dans un terminal dédié):

```bash
symfony console messenger:consume async --sleep=1
```

- `--sleep=1` réduit le polling quand la file est vide.
- Tu peux omettre `--time-limit` pour un worker en continu en dev.

2) Fallback sans worker (auto)
- Si aucun worker ne tourne, le listener HTTP exécute un batch au plus une fois toutes `state_refresh_interval_minutes` lors du premier hit HTTP.
- Pour la démo, tu peux mettre `state_refresh_interval_minutes: 5` pour observer des mises à jour régulières.

3) Actions qui déclenchent un recalcul ciblé
- Publier une sortie
- Annuler une sortie
- S’inscrire / Se désister
→ Ces actions envoient un message ciblé pour recalculer immédiatement l’état de la sortie concernée.

---

## Comment tester rapidement

Préparation
- Assure-toi d’avoir les états suivants en BDD (libellés exacts ou variantes de casse):
  - `Créée`, `Ouverte`, `Clôturée`, `Activité en cours`, `Passée`, `Annulée`.
- Vérifie que les sorties de test ont des `dateLimiteInscription`, `dateHeureDebut`, `duree`, `nbInscriptionsMax` cohérents.

Scénarios
- Ouverte ↔ Clôturée
  - Inscris des participants jusqu’à atteindre `nbInscriptionsMax` → Clôturée
  - Désiste quelqu’un avant la date limite → Ouverte si capacité et date OK
- Vers EnCours/Passée
  - Place `dateHeureDebut` à “maintenant” et `duree` à 5 minutes, observe le passage EnCours → Passée
  - Soit via le worker (tick), soit via fallback (hit HTTP)
- Annulée
  - Bouton d’annulation (organisateur/admin) → état Annulée, plus de transitions automatiques ensuite

Vérifications
- Logs applicatifs (niveau INFO):
  - `[STATE] Rafraîchissement états: X modifié(s)`
  - `Sortie #ID: état mis à jour → <libellé>`
- Base de données: l’état lié à la sortie est mis à jour.

---

## Dépannage

- “Rien ne se passe”
  - Lancer le worker: `symfony console messenger:consume async --sleep=1`
  - Ou provoquer un hit HTTP pour que le fallback s’exécute (toutes `state_refresh_interval_minutes`).
- “Des messages en attente dans la file”
  - Vider les messages d’état: 
    ```bash
    symfony console dbal:run-sql "DELETE FROM messenger_messages WHERE queue_name = 'default' AND body LIKE '%Refresh%StateMessage%'"
    ```
- “Aucun état ‘Ouverte/Clôturée/…’ en BDD”
  - Le résolveur crée l’état manquant si introuvable (sécurité dev). En prod, assurez-vous d’initialiser les états via un script de fixtures.

---

## Fichiers modifiés/ajoutés

Ajouts
- `src/Service/SortieStateResolver.php`
- `src/Service/SortieStateRefresher.php`
- `src/Message/RefreshOneSortieStateMessage.php`
- `src/Message/RefreshSortiesStateMessage.php`
- `src/MessageHandler/RefreshOneSortieStateMessageHandler.php`
- `src/MessageHandler/RefreshSortiesStateMessageHandler.php`
- `src/EventListener/StatusRefreshSchedulerListener.php`

Modifications
- `src/Controller/SortieController.php` (dispatch des messages après actions)
- `config/packages/messenger.yaml` (routing des messages)
- `config/services.yaml` (paramètres + injection du scheduler)

---

## Limites & améliorations possibles

- Index SQL: pour les performances, indexer `etat_id`, `date_limite_inscription`, `date_heure_debut`.
- Horaire précis: si on veut l’heure réelle pour `date_heure_debut`, passer la colonne en `DATETIME`.
- Désactivation du fallback: on peut ajouter un paramètre `state_refresh_fallback_enabled` si besoin.
- Tests unitaires: ajouter des tests sur `SortieStateResolver` avec une horloge mockée.

---

## TL;DR

- Les transitions d’état sont calculées par `SortieStateResolver` et déclenchées soit immédiatement à l’action (messages ciblés), soit périodiquement (tick toutes X minutes par Messenger + fallback HTTP).
- Configuration principale: `state_refresh_interval_minutes` dans `services.yaml`.
- Lancer le worker pour une expérience réactive: `symfony console messenger:consume async --sleep=1`.

