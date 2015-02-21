# [Grav MathJax Plugin][project]

> This plugin allows you to include math formulas in your web pages, either using TeX and LaTeX notation, and/or as MathML.

## About

`MathJax` is a plugin for [GetGrav.org](http://getgrav.org) and integrates the [MathJax library](http://www.mathjax.org/), a modern JavaScript-based LaTeX rendering solution for the Internet, into your Grav site.

By default, MathJax source is loaded automatically from the Internet using the
MathJax Content Delivery Network (CDN). This is a light-weight, "out-of-the-box" LaTeX rendering solution, and you can still use a custom configuration if you need.

Alternatively, you can host the MathJax library (approximately _150MB_) on your server located in your `user/data/mathjax` folder.

Mathematics inside the default delimiters will be rendered by MathJax. The
default math delimiters are $$...$$ and \\[...\\] for displayed mathematics, and
$...$ and \\(...\\) for in-line mathematics.

If you are interested in seeing this plugin in action, here is a screenshot:

![Screenshot MathJax Plugin](assets/screenshot.png "MathJax Preview")

## Installation and Updates

Installing or updating the `MathJax` plugin can be done in one of two ways. Using the GPM (Grav Package Manager) installation method or manual install or update method by downloading [this plugin](https://github.com/sommerregen/grav-plugin-mathjax) and extracting all plugin files to

    /your/site/grav/user/plugins/mathjax

For more informations, please check the [Installation and update guide](docs/INSTALL.md).

## Usage

The `MathJax` plugin comes with some sensible default configuration, that are pretty self explanatory:

### Config Defaults

```
# Global plugin configurations

enabled: true                 # Set to false to disable this plugin completely
built_in_js: true             # Use built-in JS of the plugin
weight: -5                    # Set the weight (order of execution)

# Default values for MathJax configuration.

CDN:
  enabled: true               # Use MathJax Content Delivery Network (CDN)
  url: https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML

# Global and page specific configurations

process: false                # (De-)Activate MathJax filter globally
```

If you need to change any value, then the best process is to copy the [mathjax.yaml](mathjax.yaml) file into your `users/config/plugins/` folder (create it if it doesn't exist), and then modify there. This will override the default settings.

If you want to alter the settings for one or only few pages, you can do so by adding page specific configurations into your page headers, e.g.

```
mathjax:
  process: true
```

to activate `MathJax` filter just for this page.

### Hosting the MathJax library on your server

The `MathJax` plugin allows you to either use the MathJax CDN (recommended) or to host the MathJax library on your server. For the latter case do the following:

  1. Download the latest MathJax release from https://github.com/mathjax/mathjax.
  2. Extract the contents to your Grav `user/data/mathjax` folder. Make sure that the file `mathjax.js` is present in the above folder (and not in any sub-folders).
  3. Copy the [mathjax.yaml](mathjax.yaml) file into your `users/config/plugins/` folder (create it if it doesn't exist), and then set `CDN.enabled: false`.

### JavaScript Override

Something you might want to do is to override the behavior of how MathJax will process your math formulas, and with Grav it is super easy.

Copy the javascript [js/mathjax.js](js/mathjax.js) into the `js` folder of your custom theme, and add it to the list of JS files

```
/your/site/grav/user/themes/custom-theme/js/mathjax.js
```

via `{% do assets.addJs('theme://js/mathjax.js') %}` i.e. in your **base.html.twig** file located in **antimatter/templates/partials/**.

After that set the `built_in_js` option of the `MathJax` plugin to `false`. That's it.

You can now edit, override and tweak it however you prefer. For all list of all options please consult the [MathJax Documentation](http://docs.mathjax.org/en/latest/) or the [options for the `tex2jax` script](http://docs.mathjax.org/en/latest/options/tex2jax.html) for a quick start. In the simplest case you can use

```
window.MathJax = {
  extensions: ["tex2jax.js"],
  jax: ["input/TeX","output/HTML-CSS"],
  tex2jax: {
    inlineMath: [ ['$','$'], ['\\(','\\)'] ],
    processEscapes: true
  }
};

```

## Contributing

You can contribute at any time! Before opening any issue, please search for existing issues and review the [guidelines for contributing](docs/CONTRIBUTING.md).

After that please note:

* If you find a bug or would like to make a feature request or suggest an improvement, [please open a new issue][issues]. If you have any interesting ideas for additions to the syntax please do suggest them as well!
* Feature requests are more likely to get attention if you include a clearly described use case.
* If you wish to submit a pull request, please make again sure that your request match the [guidelines for contributing](docs/CONTRIBUTING.md) and that you keep track of adding unit tests for any new or changed functionality.

### Support and donations

If you like my project, feel free to support me, since donations will keep this project alive. You can [![Flattr me](https://api.flattr.com/button/flattr-badge-large.png)][flattr] or send me some bitcoins to **1HQdy5aBzNKNvqspiLvcmzigCq7doGfLM4** whenever you want.

Thanks!

## License

Copyright (c) 2015 [Benjamin Regler][github]. See also the list of [contributors] who participated in this project.

[Licensed](LICENSE) for use under the terms of the [MIT license][mit-license].

[github]: https://github.com/sommerregen/ "GitHub account from Benjamin Regler"
[mit-license]: http://www.opensource.org/licenses/mit-license.php "MIT license"

[flattr]: https://flattr.com/submit/auto?user_id=Sommerregen&url=https://github.com/sommerregen/grav-plugin-mathjax "Flatter my GitHub project"

[project]: https://github.com/sommerregen/grav-plugin-mathjax
[issues]: https://github.com/sommerregen/grav-plugin-mathjax/issues "GitHub Issues for Grav MathJax Plugin"
[contributors]: https://github.com/sommerregen/grav-plugin-mathjax/graphs/contributors "List of contributors of the project"
