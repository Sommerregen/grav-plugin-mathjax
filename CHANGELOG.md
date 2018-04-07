# v1.6.0
## 04/07/2018

2. [](#improved)
  * Allow to customize LaTeX delimiters [#7](https://github.com/Sommerregen/grav-plugin-mathjax/issues/7) (Maybe MathJax.php Shouldn't Hard Code Delimiters?) & [#8](https://github.com/Sommerregen/grav-plugin-mathjax/issues/8) (Escape dollar sign)

# v1.5.1
## 04/12/2017

2. [](#improved)
  * Change CDN provider to CloudFlare [#6](https://github.com/Sommerregen/grav-plugin-mathjax/issues/6) (`https://cdn.mathjax.org` end-of-life)

# v1.5.0
## 08/06/2016

1. [](#new)
  * Released stable version (requires **Grav v1.1.0+** + **Breaking Change!**); please read the beta release changelogs for more details
  * Added demo content
2. [](#improved)
  * Allow `active` option to be toggleable in page options
  * Updated `README.md`
3. [](#bugfix)
  * Fixed `mathjax` Twig filter in case MathJax was disabled on the page

# v1.5.0-beta.2
## 08/06/2016

1. [](#new)
  * Renamed `process` option to `active` (**Breaking Change!**)
2. [](#improved)
  * Use tabs in admin settings
3. [](#bugfix)
  * Fixed case when page contains no formula

# v1.5.0-beta.1
## 08/05/2016

2. [](#improved)
  * Refactored code (requires **Grav v1.1.0+**)
3. [](#bugfix)
  * Use more robust MarkdownParser instead of regexes (fixes [#4](https://github.com/Sommerregen/grav-plugin-mathjax/issues/4))

# v1.4.0
## 11/18/2015

1. [](#new)
  * Added [#3](https://github.com/Sommerregen/grav-plugin-mathjax/pull/3) ("MathJax Process" toggle to page options tab)
2. [](#improved)
  * Added Twig filter documentation to `README.md`
  * Improved code
3. [](#bugfix)
  * Fixed `mathjax` Twig filter

# v1.3.2
## 09/09/2015

3. [](#bugfix)
  * Fixed "Call to a member function modified() on a non-object"

# v1.3.1
## 09/09/2015

2. [](#improved)
  * Added blueprints for Grav Admin plugin

# v1.3.0
## 08/08/2015

1. [](#new)
  * Added admin configurations **(requires Grav 0.9.34+)**
  * Added `{{% mathjax type='block|inline' %}}` shortcode
2. [](#improved)
  * Switched to `onBuildPagesInitialized` event **(requires Grav 0.9.29+)**
  * Updated `README.md`
3. [](#bugfix)
  * Fixed [#2](https://github.com/Sommerregen/grav-plugin-mathjax/issues/2) (JS script not added to page)

# v1.2.0
## 05/14/2015

1. [](#new)
  * Added usage example in [README.md](https://github.com/Sommerregen/grav-plugin-mathjax/blob/master/README.md)
2. [](#improved)
  * Add assets (CSS and JS) dynamical to page now
  * Changed default value `process: false` to `process: true` in [mathjax.yaml](https://github.com/Sommerregen/grav-plugin-mathjax/blob/master/mathjax.yaml)

# v1.1.1
## 05/10/2015

1. [](#new)
  * Support modular pages
  * Added CSS stylesheet and `built_in_css` option
2. [](#improved)
  * PSR fixes

# v1.1.0
## 02/21/2015

1. [](#new)
  * Added `<span class="mathjax">` tags around block and inline math formulas
2. [](#improved)
  * Improved and refactored code

# v1.0.0
## 02/05/2015

1. [](#new)
  * ChangeLog started...
