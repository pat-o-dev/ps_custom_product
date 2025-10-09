# 🧩 PrestaShop Custom Product

	**Module open-source** pour PrestaShop 1.7+ / 8.x  
	Permet de créer des **produits configurables avec calcul de prix dynamique** et génération automatique des déclinaisons.


## Fonctionnalités principales

	- Calcul du prix en AJAX
	- Création automatique de déclinaison
	- Ajout direct au panier avec prix spécifique 
	- Aucun override du core
	- Interface d’administration


## Formule de calcul du prix

```
Prix HT = (Base unitaire € + (Aire m² × Prix matière €/m² × Coeff matière × Facteur forme)) × Marge
    •	Base unitaire € : coût fixe de fabrication du produit
	•	Prix matière €/m² : défini par matière
	•	Coeff matière : multiplicateur par matière (densité, finition…)
	•	Facteur forme : défini dans les paramètres de forme (RECT, TRI, SQR…)
	•	Marge : définie par produit configurable

Exemple : un voile rectangulaire 2×3 m en coton (coeff 1.1, 12.5 €/m²),
avec une base de 3 €, facteur forme 1.0, marge 1.2 →
Prix HT = (3 + (6 × 12.5 × 1.1 × 1.0)) × 1.2 = 103,2 €
```


## Installation
	1.	Copier le dossier ps_custom_product dans /modules/
	2.	Installer le module depuis le back-office (Modules > Modules et services)
	3.	Configurer :
	•	les formes (onglet Formes & dimensions)
	•	les matières (onglet Matières)
	•	les produits configurables (onglet Produits)
	4.	Créer un produit “de base” et saisir son ID dans l’onglet Produits configurables



## Structure technique
	•	controllers/front/ → endpoints AJAX (calcul + ajout panier)
	•	controllers/admin/ → gestion BO (Produits, Formes, Matières)
	•	views/templates/ → templates Smarty (admin + front)
	•	classes/ → helpers et logique métier (à venir)



## Roadmap
	•	Front complet avec preview SVG et sélecteurs dynamiques
	•	Formules de calcul personnalisables par produit
	•	Gestion de texture et affichage couleur (attribute color group)
	•	Export/import JSON de configuration
	•	Compatibilité PrestaShop 9.x



## Auteur

	Patrick Genitrini
	Contact : github.com/pat-o-dev
