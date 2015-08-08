# [Installation and update guide][project]
[project]: https://github.com/sommerregen/grav-plugin-mathjax

## Installation

Installing the `MathJax` plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's Terminal (also called the command line). From the root of your Grav install type:

	bin/gpm install mathjax

This will install the `MathJax` plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/mathjax`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `mathjax`. You can find these files either on [GitHub](https://github.com/sommerregen/grav-plugin-mathjax) or via [GetGrav.org](http://getgrav.org/downloads/plugins).

You should now have all the plugin files under

	/your/site/grav/user/plugins/mathjax

>> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and a theme to be installed in order to operate.

## Updating

As development for `MathJax` continues, new versions may become available that add additional features and functionality, improve compatibility with newer Grav releases, and generally provide a better user experience. Updating `MathJax` is easy, and can be done through Grav's GPM system, as well as manually.

### GPM Update (Preferred)

The simplest way to update this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm). You can do this with this by navigating to the root directory of your Grav install using your system's Terminal (also called command line) and typing the following:

	bin/gpm update mathjax

This command will check your Grav install to see if your `MathJax` plugin is due for an update. If a newer release is found, you will be asked whether or not you wish to update. To continue, type `y` and hit enter. The plugin will automatically update and clear Grav's cache.

#### Manual Update

Manually updating `MathJax` is pretty simple. Here is what you will need to do to get this done:

* Delete the `your/site/user/plugins/mathjax` directory.
* Download the new version of the `MathJax` plugin from either [GitHub](https://github.com/sommerregen/grav-plugin-mathjax) or [GetGrav.org](http://getgrav.org/downloads/plugins).
* Unzip the zip file in `your/site/user/plugins` and rename the resulting folder to `mathjax`.
* Clear the Grav cache. The simplest way to do this is by going to the root Grav directory in terminal and typing `bin/grav clear-cache`.

>> Note: Any changes you have made to any of the files listed under this directory will also be removed and replaced by the new set. Any files located elsewhere (for example a YAML settings file placed in `user/config/plugins`) will remain intact.
