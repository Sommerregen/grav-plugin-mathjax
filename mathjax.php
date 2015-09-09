<?php
/**
 * MathJax v1.3.1
 *
 * This plugin allows you to include math formulas in your web pages,
 * either using TeX and LaTeX notation, and/or as MathML.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 *
 * @package     MathJax
 * @version     1.3.1
 * @link        <https://github.com/sommerregen/grav-plugin-mathjax>
 * @author      Benjamin Regler <sommerregen@benjamin-regler.de>
 * @copyright   2015, Benjamin Regler
 * @license     <http://opensource.org/licenses/MIT>        MIT
 * @license     <http://opensource.org/licenses/GPL-3.0>    GPLv3
 */

namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Shortcodes\BlockShortcode;

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
      'onPageInitialized' => ['onPageInitialized', 0],
      'onTwigInitialized' => ['onTwigInitialized', 0],
      'onShortcodesInitialized' => ['onShortcodesInitialized', 0]
    ];
  }

  /**
   * Initialize configuration
   */
  public function onPageInitialized() {
    if ($this->isAdmin()) {
      $this->active = false;
      return;
    }

    if ($this->config->get('plugins.mathjax.enabled')) {
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
      $raw = $page->getRawContent();

      // Save modified page content with tokens as placeholders
      $page->setRawContent(
        $this->init()->process($raw, $page->id())
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
    }
  }

  /**
   * Initialize Twig configuration and filters.
   */
  public function onTwigInitialized()
  {
    // Expose function
    $this->grav['twig']->twig()->addFunction(
      new \Twig_SimpleFunction('mathjax', [$this, 'mathjaxFunction'], ['is_safe' => ['html']])
    );
  }

  /**
   * Set needed variables to display MathJax LaTeX formulas.
   */
  public function onTwigSiteVariables()
  {
    /** @var \Grav\Common\Grav $grav */
    $grav = $this->grav;

    /** @var Page $page */
    $page = $grav['page'];
    $config = $this->mergeConfig($page);

    // Skip if process is set to false
    if (!$config->get('process', false)) {
      return;
    }

    // Add MathJax stylesheet to page
    if ($this->config->get('plugins.mathjax.built_in_css')) {
      $grav['assets']->add('plugin://mathjax/assets/css/mathjax.css');
    }

    // Add MathJax configuration file to page
    if ($this->config->get('plugins.mathjax.built_in_js')) {
      $grav['assets']->add('plugin://mathjax/assets/js/mathjax.js');
    }

    // Resolve user data path
    $data_path = $grav['locator']->findResource('user://data');

    // Check if MathJax library was properly installed locally
    $installed = file_exists($data_path.DS.'mathjax'.DS.'MathJax.js');

    // Load MathJax library
    if ($this->config->get('plugins.mathjax.CDN.enabled') || !$installed) {
      // Load MathJax library via CDN
      $cdn_url = $this->config->get('plugins.mathjax.CDN.url');
      $grav['assets']->add($cdn_url);
    } elseif ($installed) {
      // Load MathJax library from user data path
      $grav['assets']->add('user://data'.DS.'mathjax'.DS.'MathJax.js');
    }
  }

  /**
   * Filter to parse external links.
   *
   * @param  string $content The content to be filtered.
   * @param  array  $options Array of options for the External links filter.
   *
   * @return string          The filtered content.
   */
  public function externalLinksFunction($content, $params = [])
  {
    $config = $this->mergeConfig($this->grav['page'], $params);
    return $this->init()->process($content, $config);
  }

  /**
   * Register {{% mathjax %}} shortcode.
   *
   * @param  Event  $event An event object.
   */
  public function onShortcodesInitialized(Event $event)
  {
    $mathjax = $this->init();
    // Register {{% mathjax %}} shortcode
    $event['shortcodes']->register(
      new BlockShortcode('mathjax', function($event) {
        $weight = $this->config->get('plugins.mathjax.weight', -5);
        $this->enable([
          'onPageContentProcessed' => ['onPageContentProcessed', $weight]
        ]);

        // Update header to variable to bypass evaluation
        if (isset($event['page']->header()->mathjax->process)){
          $event['page']->header()->mathjax->process = true;
        }

        return $this->mathjax->mathjaxShortcode($event);
      })
    );
  }

  /** -------------------------------
   * Private/protected helper methods
   * --------------------------------
   */

  /**
   * Initialize plugin and all dependencies.
   *
   * @return \Grav\Plugin\ExternalLinks   Returns ExternalLinks instance.
   */
  protected function init()
  {
    if (!$this->mathjax) {
      // Initialize MathJax class
      require_once(__DIR__ . '/classes/MathJax.php');
      $this->mathjax = new MathJax();
    }

    return $this->mathjax;
  }
}
