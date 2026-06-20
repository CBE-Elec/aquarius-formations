# Aquarius Formations

Module Drupal de gestion des formations de plongee.

## 1. Objectif

Ce module Drupal permet de:

- Créer des groupes de membres, participants à une formations.
- Définir des formaions, avec un contenu sous formes de compétences à aquérir.
- Suivre la progression et la validation des competences sous forme d'un formulaire d'évaluation.
- Comporte une interface pour les encadrants et une interface pour les élèves, avec une vue spécifique à chaque élève.

Le module est concu pour Drupal 10/11.

## 2 Fonctionnalites

### 2.1 Définition d'une formation
Chaque formation comportera :
- un titre
- un niveau préparé (taxonomie existante "niveau de plongée", filtré par organisme = FFESSM)
- une saison (groupe existant de type "saison")
- une date de début
- un état : Pas encore ouverte, inscriptions ouvertes, formation en cours, formation terminée.
- un ou des responsable/s de la formation (group membership avec rôle "responsable de formation", filtrage parmis les utilisateurs ayant le rôle "encadrant")
- des élèves (group membership) avec un status associé.
- des compétences à valider. A voir, soit à choisir parmis une taxonomie, soit à détailler (plus lourd, donc avoir la possibilité de les copier/coller)
- des évaluations (voir plus loin)
- éventuellement des séances de cours théoriques
- éventuellement des formulaires d'examen sur la théorie

### 2.2 Gestion des élèves
Le responsable de formation peut ajouter, retirer, changer le status d'un élève.
Un élève s'inscrit en devenant membre du groupe de la formation (techniquement group membership)
Son status peut être "Inscription déposée", "Formation en cours", "Formation abandonnée", "Niveau validé" (liste à revoir).
L'utilisation du module "state_machine" est à envisager pour le status.
Une synthèse globale, basée sur ce status d'élève, de toutes les formations en cours sur une saision pourrait être intéressante.


### 2.3 Compétences
Chaque compétence comporte :
- un titre
- un niveau de plongée (si taxonomie globale utilisée)
- une description
- un critère de réussite

On peut mettre en place un système de revision / validation (dans un second temps du dev)

### 2.4 Evaluation
Chaque élève pourra être évalué plusieurs fois sur chaque compétence de la formation. 
L'évaluation est faite par un encadrant (rôle global "Encadrant").
Les évaluations seront notées : "Non travaillée" ; "Travaillée" ; ""Réussie".
Une évaluation se faire sous la forme d'un formulaire listant toutes les compétences à valider. Pour chacune, l'encadrant choisit la "note". Par défaut, "non travaillée" est sélectionée.
A voir : donner la possibilité au responsable de modifier cette liste.
Un champ permet à l'encadrant de laisser un commentaire (format texte simple)

### 2.5 Evaluation et progression
Pour chaque évaluation, on a :
- l'élève évalué (éventuellement avec sa photo)
- l'encadrant qui évalue
- la date de l'évaluation
- les conditions de l'évaluation : en piscine, en fosse, en lac, en mer. Eventuellement associé à une sorite (groupe de type "Sortie" existant sur le site), listé du plus récent au plus ancien, pas dans le futur)
- La liste des compétences avec l'évaluation correspondante, indiquer le critère d'évaluation
- un champ de commentaire libre

La synthèse des évaluations indiquera un pourcentage de progression, basé sur le nombre de compétences "réussies" par rapport au nombre total de compétences.
Une visualisation de l'aciduité de l'élève pourrait être intéressante. Basée sur le nombre d'évaluations et leur répartition temporelle

Une synthèse des évaluation est disponible pour l'élève et les encadrants. Elle indique pour chaque compétence la note maximale obtenue et éventuellement comment les notes ont progressée (dans un second temps du dev).
Les commentaires sont visibles des élèves et des encadrants.

Les élèves ne peuvent pas modifier les évaluations. Les encadarnts peuvent modifier les évaluations qu'ils ont remplis. Le responsable de formation peut modifier toutes les évaluations.
Voir pour rendre cette gestion des droits modifiable (interface classique de Drupal)

### 2.6 Droits
Les droits sont géré de façon classique pour Drupal : plusieurs droits sont défnit par le module, le concepteur peut associer des rôles à chaque droit depuis l'interface admin de Drupal.
Par défaut nous auront :
- Les encadrants (rôle global "encadrant") peuvent voir toutes les évaluations et en créer. Ils peuvent modifier leurs propres évaluations.
- Un élève (group membership) peut voir toutes les évaluations le concernant.
- Le responsable de formation (group membership avec rôle "responsable") peut modifier les inscriptions des élèves, modifier les évaluations, modifier la formation.
- les "gestionnaire de membre" (rôle global) ont les mêmes droits que les responsable de formation, plus la possibilité de créer des formations.

- Droits sur les connaissances à valider : à voir

## 3. Conception

### 3.1 Architecture du module

Nom machine propose: aquarius_formations

Ce que gère le module :
- Conteneur global pour la gestion des formations
- Ajoute un menu "Formations" dans la barre d'administration et génère les sous menus nécessaires
- Tout les éléments qui sont "figés" et ne doivent pas être modifiés par l'utilisateur (concepteur)
- Génère les paramètres modifiables par l'utilisateur / concepteur
- Génère les droits
- Fournit toutes les aides pour la génération de la synthèse des évaluaitions (calculs, champs...) via des vues.
- fournit des vues de base pour la gestion des élèves, des évaluations, les synthèses
- fournit les formulaires d'évaluation

Architecture générale :
Le module se base sur le module group de Drupal. Une formation est un groupe. Les membres sont les élèves et le responsble de la formation (avec un rôle spécifique). A voir si on inclus les encadrants ou non.
Les évaluations sont des noeuds de type "Evaluation de formation". Ce type de noeud doit être créé par ce module. Ce type de noeud sera associé au groupe.
Les listes (élèves, compétences...) et synthèses des évaluations seront concues sous forme de vue. Le module propose les vues nécessaires décrit ici, mais aussi des champs / blocs (à préciser) utilisables dans des vues créées et personalisées par le concepteur du site.


### 3.2 Points à décider
- Gestion des compétences : à voir comment on gère les compétences : taxonomie, noeud, champ (pas très pratique pour la réutilisation). Est-ce que l'on crée une bibliothèque de compétences et l'on vient piocher dedans pour chaque formation. Ou bien, on défini une liste de compétences pour chaque niveau, indépendamment des formations, et en sélectionnant le niveau préparé dans la formation, les compétences correspondantes sont utilisées. Dans ce cas, un système de versions sera néceasaire pour ne pas modifier le contenu des compétences des formations passées lorsque l'on veut modifier ces compétences pour une nouvelle formation.

- inteface utilisateurs pour les évaluations : on peut commencer par un simple code couleur. A voir si on utilise des pictogrames et des graphes pour afficher les progressions. A voir si on fait des évaluations groupées / par lot.

