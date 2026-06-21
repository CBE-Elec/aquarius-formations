# TODO technique et decisionnel - Aquarius Formations

## Priorite 1 - A faire maintenant

- [ ] Trancher le modele de competences (bloquant)
  - [ ] Taxonomie globale de competences
  - [ ] Entite dediee competence
  - [ ] Competences propres a chaque formation
- [ ] Trancher le versionning des competences
  - [ ] Snapshot des competences a l'ouverture de la formation
  - [ ] Version par niveau/saison
- [ ] Trancher le modele des evaluations
  - [ ] Type de contenu node Evaluation
  - [ ] Content entity Evaluation
- [ ] Trancher le stockage des lignes d'evaluation par competence
  - [ ] Paragraph par ligne
  - [ ] Champ multi-valeurs structure
  - [ ] Entite/table dediee
- [ ] Trancher la place des encadrants
  - [ ] Role global uniquement
  - [ ] Membre du groupe formation
- [ ] Trancher les workflows
  - [ ] Etat formation
  - [ ] Statut eleve
  - [ ] Utilisation ou non de state_machine
- [ ] Trancher le perimetre MVP
  - [ ] Inclure ou non seances theoriques
  - [ ] Inclure ou non examens theoriques
  - [ ] Inclure ou non indicateur d'assiduite

## Priorite 2 - Conception technique detaillee

- [ ] Definir les dependances techniques strictes et optionnelles
  - [ ] Group
  - [ ] Views
  - [ ] state_machine
  - [ ] paragraphs
- [ ] Definir le schema de donnees Formation (base Group)
  - [ ] Champs: titre, niveau, saison, date debut, etat
  - [ ] Regle de filtrage niveau: organisme FFESSM
- [ ] Definir le schema des memberships
  - [ ] Role membre eleve
  - [ ] Role membre responsable formation
  - [ ] Champ statut eleve + transitions
- [ ] Definir le schema des evaluations
  - [ ] References: formation, eleve, evaluateur, sortie
  - [ ] Conditions: piscine/fosse/lac/mer
  - [ ] Date evaluation
  - [ ] Commentaire texte
- [ ] Definir la formule de progression
  - [ ] Note max par competence
  - [ ] Taux = competences reussies / competences totales
- [ ] Definir l'indicateur d'assiduite (si retenu)
- [ ] Definir les index SQL necessaires
  - [ ] (formation, eleve, date)
  - [ ] (formation, competence, note)

## Priorite 3 - Acces et securite

- [ ] Definir la matrice de droits globaux Drupal
  - [ ] create formation
  - [ ] manage formation members
  - [ ] create evaluation
  - [ ] edit own evaluation
  - [ ] edit any evaluation in formation
  - [ ] view own evaluations
- [ ] Definir les regles d'acces contextuelles
  - [ ] Eleve: acces uniquement a ses evaluations
  - [ ] Encadrant: edition de ses evaluations
  - [ ] Responsable: edition de toutes les evaluations de sa formation
- [ ] Definir les cas limites de securite
  - [ ] Encadrant non lie a la formation
  - [ ] Eleve tentant d'acceder a une autre formation

## Priorite 4 - Implementation technique

- [x] Generer le squelette du module aquarius_formations
  - [x] aquarius_formations.info.yml
  - [x] aquarius_formations.module
  - [x] aquarius_formations.permissions.yml
  - [x] aquarius_formations.routing.yml
  - [x] aquarius_formations.services.yml
  - [x] liens de menu admin
- [x] Generer la structure Formation basee sur Group
  - [x] Type de groupe Formation
  - [x] Roles de membership
  - [x] Champs de groupe
- [] Generer la structure Evaluation
  - [] Bundle et champs
  - [] Formulaire de saisie par competence
- [ ] Implementer les services metier
  - [ ] Calcul progression
  - [ ] Controle des transitions de statut
  - [ ] Controle des acces metier

## Priorite 5 - Interfaces et vues

- [ ] Definir et creer les vues de base
  - [ ] Eleves par formation avec statut
  - [ ] Evaluations par formation
  - [ ] Progression par eleve
- [ ] Definir les ecrans de saisie/consultation
  - [ ] Interface encadrant (saisie evaluation)
  - [ ] Interface eleve (synthese personnelle)
  - [ ] Interface responsable (suivi global)
- [ ] Ajouter les elements visuels MVP
  - [ ] Code couleur des notes
  - [ ] Barre de progression

## Priorite 6 - Validation technique

- [ ] Ecrire les tests de logique metier
  - [ ] Calcul progression
  - [ ] Transitions de statut
- [ ] Ecrire les tests d'acces
  - [ ] Droits eleve
  - [ ] Droits encadrant
  - [ ] Droits responsable
- [ ] Mettre en place le controle qualite statique
  - [ ] PHPCS Drupal
  - [ ] PHPStan
