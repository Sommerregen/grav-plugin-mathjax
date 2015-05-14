<?php
/**
 * MathJax v1.2.0
 *
 * This plugin allows you to include math formulas in your web pages,
 * either using TeX and LaTeX notation, and/or as MathML.
 *
 * Licensed under MIT, see LICENSE.
 *
 * @package     MathJax
 * @version     1.2.0
 * @link        <https://github.com/sommerregen/grav-plugin-mathjax>
 * @author      Benjamin Regler <sommerregen@benjamin-regler.de>
 * @copyright   2015, Benjamin Regler
 * @license     <http://opensource.org/licenses/MIT>            MIT
 */

namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;

/**
 * MathJax Plugin
 *
 * This plugin allows you to include mathematics in your web pages,
 * either using TeX and LaTeX notation, and/or as MathML.
 */
class MathJaxPlugin extends Plugin
{
  /**
   * @var MathJaxPlugin
   */

  /** ---------------------------
   * Private/protected properties
   * ----------------------------
   */

  /**
   * Instance of MathJax class
   *
   * @var object
   */
  protected $mathjax;

  /**
   * Modified current page?
   *
   * @var boolean
   */
  protected $modified = false;

  /** -------------
   * Public methods
   * --------------
   */

  /**
   * Return a list of subscribed events.
   *
   * @return array    The list of events of the plugin of the form
   *                      'name' => ['method_name', priority].
   */
  public static function getSubscribedEvents()
  {
    return [
      'onPluginsInitialized' => ['onPluginsInitialized', 0],
    ];
  }

  /**
   * Initialize configuration.
   */
  public function onPluginsInitialized() {
    if ($this->isAdmin()) {
      $this->active = false;
      return;
    }

    if ($this->config->get('plugins.mathjax.enabled')) {
      // Initialize MathJax class
      require_once(__DIR__.'/classes/MathJax.php');
      $this->mathjax = new MathJax();

      $weight = $this->config->get('plugins.mathjax.weight', -5);
      // Process contents order according to weight option
      // (default: -5): to process page content right after SmartyPants

      $this->enable([
        'onPageContentRaw' => ['onPageContentRaw', 0],
        'onPageContentProcessed' => ['onPageContentProcessed', $weight],
        'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
      ]);
    }
  }

  /**
   * Add content after page content was read into the system.
   *
   * @param  Event  $event An event object, when `onPageContentRaw` is
   *                       fired.
   */
  public function onPageContentRaw(Event $event)
  {
    /** @var Page $page */
    $page = $event['page'];
    $config = $this->mergeConfig($page);

    if ($config->get('process', false)) {
      // Get raw content and substitute all formulas by a unique token
      $raw_content = $page->getRawContent();

      // Save modified page content with tokens as placeholders
      $page->setRawContent(
        $this->mathjax->process($raw_content, $page->id())
      );
    }
  }

  /**
   * Add content after page was processed.
   *
   * @param Event $event An event object, when `onPageContentProcessed`
   *                     is fired.
   */
  public function onPageContentProcessed(Event $event)
  {
    // Get the page header
    $page = $event['page'];

    // Normalize page content, if modified
    if ($this->mathjax->modified()) {
      // Get modified content, replace all tokens with their
      // respective formula and write content back to page
      $content = $page->getRawContent();
      $page->setRawContent($this->mathjax->normalize($content));

      // Set X-UA-Compatible meta tag for Internet Explorer
      $metadata = $page->metadata();
      $metadata['X-UA-Compatible'] = array(
        'http_equiv' => 'X-UA-Compatible',
        'content' => 'IE=edge'
      );
      $page->metadata($metadata);

      // Dynamically add assets only for (current) modified page
      if ($this->grav['page']->slug() == $page->slug()) {
        $this->modified = true;
      }
    }
  }

  /**
   * Set needed variables to display MathJax LaTeX formulas.
   */
  public function onTwigSiteVariables()
  {
    // Get current page and configurations

    /** @var Page $page */
    $page = $this->grav['page'];
    $config = $this->mergeConfig($page);

    // Skip if process is set to false
    if (!$config->get('process', false) || !$this->modified) {
      return;
    }

    // Add MathJax stylesheet to page
    if ($this->config->get('plugins.mathjax.built_in_css')) {
      $this->grav['assets']->add('plugin://mathjax/assets/css/mathjax.css');
    }

    // Add MathJax configuration file to page
    if ($this->config->get('plugins.mathjax.built_in_js')) {
      $this->grav['assets']->add('plugin://mathjax/assets/js/mathjax.js');
    }

    // Resolve user data path
    $locator = $this->grav['locator'];
    $data_path = $locator->findResource('user://data');

    // Check if MathJax library was properly installed locally
    $installed = file_exists($data_path.DS.'mathjax'.DS.'MathJax.js');

    // Load MathJax library
    if ($this->config->get('plugins.mathjax.CDN.enabled') || !$installed) {
      // Load MathJax library via CDN
      $cdn_url = $this->config->get('plugins.mathjax.CDN.url');
      $this->grav['assets']->add($cdn_url);
    } elseif ($installed) {
      // Load MathJax library from user data path
      $this->grav['assets']->add('user://data'.DS.'mathjax'.DS.'MathJax.js');
    }
  }
}
