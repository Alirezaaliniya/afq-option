# _legacy — original functions.php snippets

These are the ten files the plugin was built from. They are **not loaded**
by WordPress: only `afq-option.php` in the plugin root is, and nothing
requires anything from this folder.

They are kept only so the conversion can be diffed. Every function, meta
key, CSS class, element ID, asset handle and shortcode in them was carried
over verbatim — the inline CSS/JS strings became real files under
`/assets`, and the rest moved into `/includes`.

Where each file went:

| Legacy file | Now lives in |
|---|---|
| `car-post-type.php` | `includes/post-types.php`, `includes/elementor/tags.php` |
| `Afq-car-cat-archive-image-tag.php` | `includes/elementor/tags.php` |
| `car-image-metabox.php` | `includes/admin/metabox-car-media.php`, `includes/admin/taxonomy-fields.php`, `includes/helpers.php`, `includes/elementor/tags.php`, `assets/css/admin-car-media.css`, `assets/js/admin-car-media.js` |
| `carmetaboxes.php` | `includes/config.php`, `includes/admin/metabox-car-specs.php`, `includes/admin/metabox-car-details.php`, `includes/helpers.php`, `includes/elementor/tags.php`, `assets/css/admin-car-specs.css`, `assets/css/admin-car-details.css`, `assets/js/admin-car-specs.js`, `assets/js/admin-car-details.js` |
| `car-spot-shortcode.php` | `includes/frontend/shortcode-car-spot.php`, `assets/css/car-spot.css`, `assets/js/car-spot.js` |
| `post-type-afq-question.php` | `includes/post-types.php`, `includes/frontend/shortcode-faq.php`, `assets/css/faq.css`, `assets/js/faq.js` |
| `post-type-customer-voice.php` | `includes/post-types.php`, `includes/admin/metabox-voice.php`, `includes/frontend/shortcode-voice-grid.php`, `assets/css/admin-voice.css`, `assets/css/voice-grid.css`, `assets/js/admin-voice.js`, `assets/js/voice-grid.js` |
| `namayandegan.php` | `includes/post-types.php`, `includes/config.php`, `includes/ajax.php`, `includes/admin/metabox-rep.php`, `includes/admin/taxonomy-fields.php`, `includes/frontend/shortcode-rep-map.php`, `assets/css/admin-rep.css`, `assets/css/rep-map.css`, `assets/js/rep-map.js` |
| `sell-program.php` | `includes/post-types.php`, `includes/helpers.php`, `includes/admin/metabox-circular.php`, `includes/frontend/shortcode-circular-cars.php`, `assets/css/admin-circular.css`, `assets/css/circular-cars.css`, `assets/js/admin-circular.js` |
| `afq-signup-form.php` | `includes/post-types.php`, `includes/config.php`, `includes/helpers.php`, `includes/ajax.php`, `includes/admin/metabox-signup.php`, `includes/frontend/shortcode-signup-form.php`, `assets/css/admin-signup.css`, `assets/css/signup-form.css`, `assets/js/signup-form.js` |

Once the site has been verified, this whole folder can be deleted.
