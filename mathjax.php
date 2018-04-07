<?php
/**
 * MathJax v1.6.0
 *
 * This plugin allows you to include math formulas in your web pages,
 * either using TeX and LaTeX notation, and/or as MathML.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 *
 * @package     MathJax
 * @version     1.6.0
 * @link        <https://github.com/sommerregen/grav-plugin-mathjax>
 * @author      Benjamin Regler <sommerregen@benjamin-regler.de>
 * @copyright   2017+, Benjamin Regler
 * @license     <http://opensource.org/licenses/MIT>        MIT
 * @license     <http://opensource.org/licenses/GPL-3.0>    GPLv3
 */

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Data\Blueprints;
use Grav\Plugin\Shortcodes\BlockShortcode;

use RocketTheme\Toolbox\Event\Event;

/**
 * MathJaxPlugin
 * @package Grav\Plugin
 */
class MathJaxPlugin extends Plugin
{
    /**
     * Instance of MathJax class
     *
     * @var Grav\Plugin\MathJax
     */
    protected $mathjax;

    /**
     * Return a list of subscribed events.
     *
     * @return array    The list of events of the plugin of the form
     *                      'name' => ['method_name', priority].
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onPluginsInitialized()
    {
        // Set admin specific events
        if ($this->isAdmin()) {
            $this->active = false;
            $this->enable([
                'onBlueprintCreated' => ['onBlueprintCreated', 0]
            ]);
            return;
        }

        // Initialize MathJax class
        require_once(__DIR__ . '/classes/MathJax.php');
        $this->mathjax = new MathJax();

        // Process contents order according to weight option
        // (default: -5): to process page content right after SmartyPants
        $weight = $this->config->get('plugins.mathjax.weight', -5);

        // Register events
        $this->enable([
            'onShortcodesInitialized' => ['onShortcodesInitialized', 0],
            'onMarkdownInitialized' => ['onMarkdownInitialized', 0],
            'onPageContentRaw' => ['onPageContentRaw', 0],
            'onPageContentProcessed' => ['onPageContentProcessed', $weight],
            'onTwigInitialized' => ['onTwigInitialized', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ]);
    }

    /**
     * Extend page blueprints with MathJax configuration options.
     *
     * @param Event $event
     */
    public function onBlueprintCreated(Event $event)
    {
        /** @var Blueprints $blueprint */
        $blueprint = $event['blueprint'];

        if ($blueprint->get('form/fields/tabs')) {
            $blueprints = new Blueprints(__DIR__ . '/blueprints');
            $extends = $blueprints->get($this->name);
            $blueprint->extend($extends, true);
        }
    }

    /**
     * Handle the markdown initialized event.
     *
     * @param  Event  $event The event containing the markdown parser
     */
    public function onMarkdownInitialized(Event $event)
    {
        /** @var Grav\Common\Markdown\Parsedownextra $markdown */
        $markdown = $event['markdown'];
        $this->mathjax->setupMarkdown($markdown);
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
        $enabled = ($config->get('enabled') && $config->get('active')) ? true : false;

        // Reset MathJax instance
        $this->mathjax->reset();
        $this->mathjax->enabled($enabled);

        if ($enabled) {
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
     * Add content after page was processed.
     *
     * @param Event $event An event object, when `onPageContentProcessed`
     *                     is fired.
     */
    public function onPageContentProcessed(Event $event)
    {
        // Get the page header
        $page = $event['page'];

        $config = $this->mergeConfig($page);
        $enabled = ($config->get('enabled') && $config->get('active')) ? true : false;

        // Get modified content, replace all tokens with their
        // respective formula and write content back to page
        $type = $enabled ? 'html' : 'raw';
        $content = $page->getRawContent();
        $page->setRawContent($this->mathjax->normalize($content, $type));
    }

    /**
     * Initialize Twig configuration and filters.
     */
    public function onTwigInitialized()
    {
        // Expose function
        $this->grav['twig']->twig()->addFilter(
            new \Twig_SimpleFilter('mathjax', [$this, 'mathjaxFilter'], ['is_safe' => ['html']])
        );
    }

    /**
     * Set needed variables to display MathJax LaTeX formulas.
     */
    public function onTwigSiteVariables()
    {
        /** @var \Grav\Common\Assets $assets */
        $assets = $this->grav['assets'];

        /** @var Page $page */
        $page = $this->grav['page'];

        // Skip if active is set to false
        $config = $this->mergeConfig($page);
        if (!($config->get('enabled') && $config->get('active'))) {
            return;
        }

        // Reset MathJax instance and enable parser
        $this->mathjax->reset();
        $this->mathjax->enabled(true);

        // Add MathJax stylesheet to page
        if ($this->config->get('plugins.mathjax.built_in_css')) {
            $assets->add('plugins://mathjax/assets/css/mathjax.css');
        }

        // Add MathJax configuration file to page
        if ($this->config->get('plugins.mathjax.built_in_js')) {
            $assets->add('plugins://mathjax/assets/js/mathjax.js');
        }

        // Resolve user data path
        $data_path = $this->grav['locator']->findResource('user://data');

        // Check if MathJax library was properly installed locally
        $installed = file_exists($data_path . DS .'mathjax' . DS . 'MathJax.js');

        // Load MathJax library
        if ($this->config->get('plugins.mathjax.CDN.enabled') || !$installed) {
            // Load MathJax library via CDN
            $assets->add($this->config->get('plugins.mathjax.CDN.url'));
        } elseif ($installed) {
            // Load MathJax library from user data path
            $assets->add('user://data/mathjax/MathJax.js');
        }
    }

    /**
     * Filter to parse MathJax formula.
     *
     * @param  string $content The content to be filtered.
     * @param  array  $options Array of options for the MathJax formula filter.
     *
     * @return string          The filtered content.
     */
    public function mathjaxFilter($content, $params = [])
    {
        // Get custom user configuration
        $page = func_num_args() > 2 ? func_get_arg(2) : $this->grav['page'];
        $config = $this->mergeConfig($page, true, $params);

        // Enable parser
        $this->mathjax->enabled(true);

        // Render content
        $content = $this->mathjax->render($content, $config, $page);
        $content = $this->mathjax->normalize($content);

        // Reset MathJax instance
        $this->mathjax->reset();
        return $content;
    }

    /**
     * Register {{% mathjax %}} shortcode.
     *
     * @param  Event  $event An event object.
     */
    public function onShortcodesInitialized(Event $event)
    {
        // Register {{% mathjax %}} shortcode
        $event['shortcodes']->register(
            new BlockShortcode('mathjax', function($event) {
                $weight = $this->config->get('plugins.mathjax.weight', -5);
                $this->enable([
                  'onPageContentProcessed' => ['onPageContentProcessed', $weight]
                ]);

                // Update header variable to bypass evaluation
                if (isset($event['page']->header()->mathjax->process)){
                  $event['page']->header()->mathjax->process = true;
                }

                return $this->mathjax->mathjaxShortcode($event);
            })
        );
    }
}
