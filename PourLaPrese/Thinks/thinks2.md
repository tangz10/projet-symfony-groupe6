# Journal cumulatif — Tests voters (REGISTER, EDIT)

Ce document conserve l’historique complet des assouplissements temporaires et ajoute les nouveaux liés au test de `SORTIE_EDIT`. Rien n’est supprimé, tout est tracé pour restauration ultérieure.

---

## [NOUVEAU] Tests `SORTIE_EDIT`

Changements appliqués (TEMPORAIRES)

1) Voter `App\Security\Voter\SortieVoter`
- Règle de décision pour `SORTIE_EDIT`:
  - Refus si anonyme (non connecté).
  - Autorisé si l’utilisateur a `ROLE_ADMIN`.
  - Autorisé si l’utilisateur est l’organisateur de la sortie.
  - Sinon: refus.
  - Les règles métier fines (ex. état "Créée") restent côté contrôleur pour conserver les messages existants.

2) Contrôleur `src/Controller/SortieController.php`
- Action `edit`:
  - Attribut `#[IsGranted('ROLE_USER')]` désactivé temporairement pour tester le voter en anonyme (on laisse passer jusqu’au contrôleur).
  - En tête de méthode: contrôle "doux" via `isGranted('SORTIE_EDIT', $sortie)`; si refus (anonyme/non autorisé), pas de 403 → on ajoute un flash d’erreur et on redirige vers la page de la sortie.
  - Les vérifications existantes sont conservées, dont:
    - Organisateur OU Admin requis (message existant si non autorisé).
    - État "Créée" requis (message existant si autre état).

3) Template `templates/sortie/show.html.twig`
- Ajout d’un CTA temporaire visible pour les anonymes quand la sortie est à l’état "Créée":
  - Lien « Tester l’édition » vers `app_sortie_edit` (GET) pour déclencher le voter puis afficher le message flash.
  - Bouton "Modifier" affiché pour l’organisateur OU l’admin lorsque la sortie est "Créée".
  - Aucun effet de bord en GET; le formulaire d’édition reste réservé aux utilisateurs autorisés.

Restauration après tests `SORTIE_EDIT`
- Réactiver `#[IsGranted('ROLE_USER')]` sur l’action `edit`.
- Option: utiliser `denyAccessUnlessGranted('SORTIE_EDIT', $sortie)` si vous préférez un 403 plutôt qu’un flash.
- Retirer le CTA anonyme « Tester l’édition » du template.
- Le voter `EDIT` (organisateur OU admin) peut rester tel quel ou être ajusté selon votre politique.

---

## [EXISTANT] Tests `SORTIE_REGISTER` (rappel)

- Template `show.html.twig`:
  - Ajout d’un bouton « S’inscrire » visible aussi pour anonymes lorsque `canTryRegister` est vrai. Lien en GET vers `app_sortie_inscrire` pour déclencher le voter puis message flash.
- Contrôleur `SortieController::inscrire()`:
  - Méthodes temporaires: `['POST','GET']` (GET sans side effect, POST pour l’inscription réelle).
  - Attribut `#[IsGranted('ROLE_USER')]` désactivé temporairement; décision d’accès via `isGranted('SORTIE_REGISTER', $sortie)` avec message flash au lieu d’un 403 si refus.
  - Toutes les validations et messages existants sont conservés (CSRF, déjà inscrit, complet, date dépassée, état...).
- Voter `SortieVoter::REGISTER`:
  - Exige un `Participant` connecté (anonyme refusé), le reste est géré par la logique existante.

Restauration après tests `REGISTER`
- Revenir à `methods: ['POST']` uniquement sur `inscrire()`.
- Réactiver `#[IsGranted('ROLE_USER')]` sur `inscrire()`.
- Retirer le bouton anonyme dans `show.html.twig`.

---

## Historique conservé (note précédente)

But: pouvoir tester le SortieVoter sans redirection vers /login et sans 403, avec un message flash côté UI.

Modifs appliquées (à remettre après vos tests)

1) SortieController — garde d’authentification de classe
- Supprimé temporairement: `#[IsGranted('IS_AUTHENTICATED_FULLY')]` sur la classe `SortieController`.
- Effet: les pages/actions du contrôleur ne redirigent plus automatiquement vers /login.
- Remise en place: rajouter l’attribut au-dessus de la classe:
  ```php
  #[IsGranted('IS_AUTHENTICATED_FULLY')]
  class SortieController extends AbstractController { /* ... */ }
  ```

2) Route d’inscription — ne pas lever 403, afficher un message
- Supprimé temporairement: `#[IsGranted('ROLE_USER')]` sur la méthode `inscrire()`.
- Remplacé `denyAccessUnlessGranted('SORTIE_REGISTER', $sortie)` par un contrôle "doux":
  ```php
  if (!$this->isGranted('SORTIE_REGISTER', $sortie)) {
      $this->addFlash('error', "Il faut être connecté en tant que participant pour s'inscrire à une sortie.");
      return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
  }
  ```
- Effet: si l’utilisateur n’est pas un Participant, on affiche un message flash au lieu d’un 403.
- Remise en place (après tests):
  - Réactiver l’attribut `#[IsGranted('ROLE_USER')]` sur la méthode.
  - Remettre éventuellement `denyAccessUnlessGranted('SORTIE_REGISTER', $sortie, '...')` si vous préférez un 403 (ou conserver le comportement "message flash").

3) Template `templates/sortie/show.html.twig` — rendre visible le bouton “S’inscrire” pour les anonymes (TEMP)
- Changement TEMPORAIRE: le bouton “S’inscrire” s’affiche aussi quand `me` est `null` si les conditions fonctionnelles sont remplies (sortie ouverte, pas complète, avant limite, etc.).
- Code ajouté: une branche `elseif not me and canTryRegister` qui affiche le même `<form>` d’inscription (avec CSRF).
- Effet: les utilisateurs non connectés voient le bouton, cliquent → la requête POST passe par le voter; comme ils ne sont pas Participant, on affiche le flash: « Il faut être connecté en tant que participant pour s'inscrire à une sortie. »
- Revenir au comportement initial: supprimer cette branche Twig pour les anonymes et conserver uniquement l’affichage du bouton pour `me` connecté.

Conseils de test
- En anonyme: POST sur `/sortie/{id}/inscrire` retourne un message flash "Il faut être connecté en tant que participant..." et redirige vers la page de la sortie.
- Connecté (Participant): le voter autorise, puis vos validations existantes s’appliquent (messages: complet, date limite dépassée, déjà inscrit, état...).
- Web Profiler → Security: vérifier le vote sur `SORTIE_REGISTER` (granted/denied) par `App\Security\Voter\SortieVoter`.

Rétablissement complet (prod/dev classique)
- Réactiver `#[IsGranted('IS_AUTHENTICATED_FULLY')]` sur `SortieController`.
- Réactiver `#[IsGranted('ROLE_USER')]` sur `inscrire()`.
- Retirer la branche Twig affichant le bouton “S’inscrire” pour les anonymes.
- Option: repasser à `denyAccessUnlessGranted('SORTIE_REGISTER', $sortie, '...')` si vous préférez un 403 personnalisé plutôt que le message flash.

---

## [MÀJ] Restauration REGISTER + EDIT (avant tests CANCEL)

Ce qui a été remis à l’état initial:
- `inscrire()`
  - Route: `methods: ['POST']` uniquement.
  - Attribut: `#[IsGranted('ROLE_USER')]` réactivé.
  - Le bouton anonyme “S’inscrire” a été retiré du template.
- `edit()`
  - Attribut: `#[IsGranted('ROLE_USER')]` réactivé.
  - Le CTA anonyme “Tester l’édition” a été retiré du template.
  - Le voter `SORTIE_EDIT` reste en place (organisateur OU admin), les messages existants sont conservés.

Front: retour à l’affichage initial (pas de CTA de test pour REGISTER/EDIT).

Restauration confirmée avant d’entamer les tests `SORTIE_CANCEL`.

---

## [NOUVEAU] Tests `SORTIE_CANCEL` (temporaire)

Changements appliqués pour permettre le test du voter en anonyme:

1) Voter `SortieVoter::CANCEL`
- Règle: anonyme refusé; autorisé si `ROLE_ADMIN` ou si l’utilisateur est l’organisateur; sinon refus.

2) Contrôleur `SortieController::annuler()`
- Route: `methods: ['POST','GET']` TEMPORAIREMENT, pour permettre de déclencher le voter en GET sans effet de bord.
- Attribut `#[IsGranted('ROLE_USER')]`: désactivé TEMPORAIREMENT pour ce test (la décision passe par le voter).
- Logique: en tête, `isGranted('SORTIE_CANCEL', $sortie)`; si refus → message flash “Seul l'organisateur de la sortie ou un administrateur peut annuler une sortie.” et redirection vers la page de la sortie.
- En GET: aucun effet de bord, on revient après passage dans le voter.
- En POST: contrôles existants conservés (CSRF, organisateur/admin, état déjà commencé, etc.) et messages identiques.

3) Template `templates/sortie/show.html.twig`
- Ajout d’un CTA TEMPORAIRE “Tester l’annulation” visible pour les anonymes quand la sortie est à l’état “Créée”. Lien en GET vers `app_sortie_annuler` afin de déclencher le voter et afficher le message.

À RESTAURER après les tests `CANCEL`
- Remettre `annuler()` en `methods: ['POST']` uniquement.
- Réactiver `#[IsGranted('ROLE_USER')]` sur `annuler()`.
- Retirer le CTA anonyme “Tester l’annulation” du template.

Notes
- Les sections précédentes du journal (REGISTER, EDIT) restent inchangées et consultables ci-dessus pour remise en place complète si nécessaire.

---

## [RÉTABLI] Restauration complète après tests `CANCEL`

Appliqué le: 2025-10-15

Back (sécurité):
- `SortieController` — garde d’authentification de classe réactivée: `#[IsGranted('IS_AUTHENTICATED_FULLY')]` (toutes les actions du contrôleur nécessitent une authentification complète, dont `show`).
- `annuler()` — RESTAURÉ:
  - Route: `methods: ['POST']` uniquement.
  - Attribut: `#[IsGranted('ROLE_USER')]` réactivé.
  - Contrôle via voter conservé: `isGranted('SORTIE_CANCEL', $sortie)` → message flash si refus.
  - CSRF obligatoire, validations et messages existants inchangés.
- `inscrire()` et `edit()` — déjà restaurés précédemment (voir section MÀJ): `methods: ['POST']` + `#[IsGranted('ROLE_USER')]` et contrôle via voters (`REGISTER`, `EDIT`).

Front:
- `templates/sortie/show.html.twig` — CTA anonymes de test supprimés (REGISTER/EDIT/CANCEL). Le commentaire d’aide “État initial: pas de CTA anonyme d’inscription” est conservé à titre documentaire, mais aucun bouton de test n’est affiché aux anonymes.
- Résultat: la page show n’est plus atteignable par un anonyme (garde de classe), et l’UI est revenue à l’état initial avant la phase de tests.

Trace et réversibilité:
- Toutes les modifications temporaires et leurs restaurations sont documentées ci-dessus pour permettre une remise en place rapide si besoin.



HUMAIN MOI HUMAIN: 
EDIT = Organisateur OU Admin OK 
REGISTER = Participant connecté OK
CANCEL = Organisateur OU Admin OK
UNREGISTER = Participant connecté OK

VOTERS SORTIE OK 
