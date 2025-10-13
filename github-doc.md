# ToDo & Co - Guide de collaboration

Vous souhaitez apporter des modifications au projet ?\
Ce document décrit les règles et bonnes pratiques à respecter pour contribuer efficacement et garantir la qualité du code.

---

## GitHub

### Infos à savoir 

Branches principales : 
- **main** : branche stable, code validé et prêt pour la production.
- **develop** : branche de développement, intègre les nouvelles fonctionnalités avant fusion dans `main`.
- **feature/...** : branche d'intégration de nouvelle fonctionnalité

### Étapes :

1.  **Créer une tâche (Issue)**\
    Donnez un **titre** et une **description** :
    - Problème
    - Besoin
    - Scénario utilisateur
    - Tâches
    - Critères d'acceptation

    Ajoutez un ou plusieurs **labels** (`feature`, `bug`, `docs`)

2.  **Cloner le dépôt**

    ``` bash
    git clone https://github.com/kayuden/to_do_and_co.git
    ```

3.  **Créer une branche pour développer votre fonctionnalité**

    ``` bash
    git checkout -b feature/nom-fonctionnalite
    ```

    Utilisez des noms de branches explicites :
    - `feature/...` pour une nouvelle fonctionnalité
    -   `fix/...` pour une correction de bug
    - `docs/...` pour la documentation

4.  **Développer vos changements**

    - Respectez les règles de qualité définies plus bas.
    - Ajoutez ou mettez à jour la documentation si nécessaire.
    - Vérifiez que vos modifications n'introduisent pas de régressions.

5.  **Commit et push**

    ``` bash
    git add .
    git commit -m "feat: [courte description]"
    git push origin feature/nom-fonctionnalite
    ```

6.  **Ouvrir une Pull Request (PR)**

    - Décrivez clairement vos changements et leur utilité.
    - Mentionnez les issues associées si applicable (`Related to #12`).
    - La PR sera relue par au moins un autre développeur avant fusion.

---

## Processus de qualité

- **Tests** :
    - Chaque fonctionnalité doit être couverte par des tests unitaires.
    - **Couverture de code minimale** : 70%.

---

## Règles à respecter

- **Style des commits** :
    Suivre la convention [Conventional
    Commits](https://www.conventionalcommits.org/).\
    Exemples :
    - `feat: ajout d’une nouvelle fonctionnalité`
    - `fix: correction d’un bug d’affichage`
    - `docs: mise à jour du guide d’utilisation`
- **Structure du code** :
    - Respecter l'architecture existante
    - Factoriser le code pour éviter la duplication.
    - Ajouter des commentaires uniquement si le code n'est pas auto-explicite.
- **Documentation** :
    - Tout ajout doit être documenté dans le `README.md` ou dans la doc correspondante.
    - Les PR doivent inclure une description claire.

---

Merci de contribuer à ce projet !
