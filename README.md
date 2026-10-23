# BeAPI Blocks Theme

## Installation

Vous devrez d'abord installer les dépendances :
```bash
composer install
yarn
```

Après l'installation, vous pouvez utiliser les commandes suivantes pour gérer l'environnement local :
```bash
# Démarrer l'environnement local (disponible à http://localhost:8889)
yarn wp-env start

# Arrêter l'environnement local
yarn wp-env stop

# Réinitialiser la base de données (supprimera toutes les données de manière permanente)
yarn wp-env clean

# Supprimer l'environnement local (supprimera toutes les données et fichiers de manière permanente)
yarn wp-env destroy
```

Après l'installation, vous pouvez utiliser les commandes suivantes pour la génération des assets :
```bash
# Observe les modifications du dossier src
yarn start

# Génère les assets pour la production
yarn build
```

## Assets splitting

``` bash
|_ src
    |_ js
        |_ common # Fichiers editor.js, index.js ...
        |_ template # Fichier nommés selon la class du body (home.js, blog.js, single.js, single-post.js, ...)
        |_ wp-block # Fichiers nommés selon la class du block sans wp-block- (ex: button.js, beapi-icon.js...)
        |_ wp-pattern # Fichiers nommés selon la class du pattern sans wp-pattern-
    |_ scss
        |_ common # Fichiers editor.scss, style.scss ...
        |_ template # Fichier nommés selon la class du body (home.scss, blog.scss)
        |_ wp-block # Fichiers nommés selon la class du block sans wp-block- (ex: button.scss, beapi-icon.scss...)
        |_ wp-pattern # Fichiers nommés selon la class du pattern sans wp-pattern-
```

### Chargement des assets

Le chargment des fichiers présents dans les dossiers `common/` doit être déclaré explicitement dans le fichier `Assets.php`. Le chargement des assets est automatique pour les fichiers présents dans les dossier `template/`, `wp-block/` et `wp-pattern/`.

Les assests des dossiers `wp-block/` sont ajoutés aux metadatas des blocs. Pour les dossiers `template/` et `wp-pattern/`, les assets sont chargés en fonction des classes présentes sur les blocks et sur les classes présentes à l'appel de la fonction php `get_body_class()`. Point de vigilance, la fonction `get_body_class()` est appellée au moment du hook `wp_enqueue_scripts`, des classes customs peuvent potentiellement être absentes.

Pour l'éditeur, la totalité des assets est chargée sans détection.

#### Exemples de nommage
- `wp-block/button.scss` sera chargé si un bloc button est présent dans la page.
- `wp-block/beapi-icon.scss` sera chargé si un bloc beapi icon est présent dans la page.
- `wp-block/columns.scss` sera chargé si un bloc columns est présent dans la page.
- `wp-pattern/card.scss` sera chargé si un bloc ayant la class `wp-pattern-card` est présent dans la page.
- `wp-template/home.scss` sera chargé si la balise `body` possède la class `home`

### Exposition des ressources JS communes

Le fichier `index.js` et `editor.js` importent le fichier `js/utils/beapi.js` qui expose les classes, instances et objets communs :
```js
import AbstractDomElement from '../classes/AbstractDomElement';
import scrollDirection from '../classes/ScrollDirection';
import * as oneloop from 'oneloop.js';
import extend from './extend';

// ----
// First declaration of beapi object is normally done in Assets.php
// ----
window.beapi = window.beapi || {};

// ----
// Expose classes and libraries for partial assets (wp-block, wp-pattern, template)
// ----
extend(window.beapi, {
	classes: {
		AbstractDomElement,
	},
	libraries: {
		oneloop,
	},
	instances: {
		scrollDirection,
	},
});
```

L'objet `beapi` est ensuite accessible dans les fichiers partiels, voir le fichier `js/wp-block/button.js` :
```js
/**
 * Button block
 */

const AbstractDomElement = window.beapi.classes.AbstractDomElement;
const oneloop = window.beapi.libraries.oneloop;
const scrollDirection = window.beapi.instances.scrollDirection;

class Button extends AbstractDomElement {
	constructor(element, options) {
		const instance = super(element, options);

		// avoid double init :
		if (!instance.isNewInstance()) {
			return instance;
		}

		// eslint-disable-next-line no-console
		console.log(
			'[Button] constructor',
			this._element,
			oneloop,
			scrollDirection
		);
	}
}

Button.init('.wp-block-button');
```

## Theme.json

Le fichier `theme.json`, stocké à la racine du thème, est utilisé pour configurer le thème dans l'interface Gutenberg. Il est généré automatiquement à partir des fichiers json dans `src/theme-json/`. 

**Il ne faut pas l'éditer manuellement !**

Le plugin `WebpakThemeJsonPlugin` dans le dossier `config/` qui se charge de la concaténation génère également le fichier `_theme-json.scss` dans le dossier `src/scss/00-variables/`. Les variables de couleurs ainsi que les variables customs y sont exportées :

```json
{
	"version": 3,
	"$schema": "https://schemas.wp.org/trunk/theme.json",
	"settings": {
		"custom": {
			"h1": {
				"lineHeight": "1.11"
			},
		}
	}
}
```

Deviendra dans le fichier `_theme-json.scss` :

```scss
$settings-custom-h1-line-height: 1.11
```

### Convention de nommage

Les fichiers sont nommés suivant la profondeur de l’objet, ainsi le fichier contenant le json suivant :

```json
{
    "version": 3,
    "$schema": "https://schemas.wp.org/trunk/theme.json",
    "styles": {
        "blocks": {
            "core/paragraph": {
                ...
            }
        }
    }
}
```

Sera nommé :

```bash
styles-blocks-coreParagraph.json
```

## Core Web Vitals

### Ce que le thème fournit

- **Image LCP** : passez `'is_lcp' => true` dans les réglages de `get_the_image()` pour l’image principale au-dessus de la ligne de flottaison (hero, couverture). Cela retire `loading="lazy"` et ajoute `fetchpriority="high"`. Définissez `decoding` dans `$attributes` uniquement si une mesure le justifie (ex. WebPageTest). Une seule image par page. Par défaut `is_lcp => false` (opt-in).

```php
the_image( $image_id, [ 'alt' => '' ], [ 'size' => 'hero', 'is_lcp' => true ] );
```

- **ARI (Advanced Responsive Images)** : sur les projets avec ARI, ne pas activer `is_lcp` sur les images déjà gérées par ARI (taille / `data-location` au bon format). ARI pilote la priorité et le lazy-load ; `is_lcp` sur la même image est redondant ou incompatible. Réserver `is_lcp` aux projets sans ARI, ou valider manuellement (page hero avec ARI, sans `is_lcp`).

- **Images décoratives (`alt=""`)** — même objectif, deux helpers selon la source :
  - **Médiathèque** (`the_image` / `get_the_image`) : passez `'alt' => ''` explicitement si l’image est décorative. Sinon WordPress reprend l’alt saisi dans le BO. Omettre la clé `alt` n’est pas équivalent à `alt=""`.
  - **Fichiers buildés** dans `dist/` (`the_theme_static_image` / `get_theme_static_image_markup`) : décoratif par défaut (`alt=""` si vous ne passez pas `alt`). Indiquez un texte d’alt si l’image porte du sens (ex. logo + nom du site). Ne pas utiliser `the_image` pour ces fichiers (pas d’ID attachment).
- **Images statiques du thème** (placeholders, illustrations dans `dist/`) : le helper statique ajoute aussi `width` et `height` via `dist/image-dimensions.json` (`imageDimensions`, généré au build) pour limiter le CLS, sans lecture disque répétée lorsque le manifeste est présent. Hors manifeste : une lecture par chemin et par requête.

```php
// Média (décoratif)
the_image( $image_id, [ 'alt' => '', 'class' => 'card__thumb' ], [ 'size' => 'card' ] );

// Asset dist/ (décoratif par défaut)
the_theme_static_image( 'dist/images/placeholder.svg', [ 'class' => 'card__image', 'loading' => 'lazy' ] );

// Asset dist/ (alt porteur de sens)
the_theme_static_image( 'dist/images/logo.svg', [ 'alt' => get_bloginfo( 'name' ), 'class' => 'header__logo' ] );
```

- **Preload des polices** : opt-in, uniquement pour les polices rendues au premier affichage. Indiquez le chemin **après build** (ex. polices Fontsource ou custom dans `dist/fonts/`, en `.woff2`). Sur un thème bloc, ne préchargez que les fichiers réellement utilisés au premier rendu (cohérents avec `theme.json`).

```php
add_filter( 'bea_theme_framework_preload_fonts', fn() => [ 'dist/fonts/my-font-regular.woff2' ] );
```

- **JS** : `Animation` s’appuie sur `IntersectionObserver` (pas de boucle au scroll). `deferInitialization` est exposé sur `window.beapi.utils` pour exécuter du code non critique lorsque le navigateur est inactif (INP).

### Ce que chaque projet doit encore configurer

Cela dépend de l’hébergement, des extensions et du contenu ; ce n’est donc pas inclus dans le thème :

- Cache de page et réglages WP Rocket (suppression du CSS inutilisé, différer / retarder le JS et leurs exclusions, exclusions du lazyload pour l’image LCP, preconnect limités aux tiers chargés tôt).
- `robots.txt` (endpoints techniques et AJAX) et en-têtes `Cache-Control` pour les assets statiques.
- Réglages du plugin SEO : URLs canoniques sur pagination personnalisée, doublons de données structurées.
- Polices : sous-ensemble des fichiers aux graisses et glyphes réellement utilisés, suppression des fichiers inutiles (italiques, graisses supplémentaires).
- Intégrations tierces (vidéos, formulaires, webinars) : chargement à l’interaction ou à l’entrée dans le viewport.
- Assets des extensions : retirer les scripts et styles front (dequeue) sur les pages qui ne les utilisent pas.
