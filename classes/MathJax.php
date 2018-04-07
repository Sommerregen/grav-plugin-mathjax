<?php
/**
 * MathJax
 *
 * This file is part of Grav MathJax plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin;

use Grav\Common\Grav;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class MathJax
 * @package Grav\Plugin\MathJax
 */
class MathJax
{
    /**
     * Markdown instance.
     *
     * @var \Grav\Common\Parseown\Parsedown
     */
    protected $markdown;

    /**
     * A unique identifier
     *
     * @var string
     */
    protected $id;

    /**
     * A key-valued array used for hashing math formulas of a page
     *
     * @var array
     */
    protected $hashes = [];

    /**
     * Toggle to enabled or disable MathJax parsing
     *
     * @var bool
     */
    protected $enabled = true;

    /**
     * Enable or disable MathJax parsing or get the state.
     *
     * @param  bool $enable TRUE to enable this plugin per page, FALSE
     *                      otherwise.
     * @return bool         Return the active state of the plugin
     */
    public function enabled($enable = null)
    {
        if (is_bool($enable)) {
            $this->enabled = (bool) $enable;
        }

        return $this->enabled;
    }

    /**
     * Process contents i.e. apply filer to the content.
     *
     * @param  string     $content The content to render.
     * @param  array      $options Options to be passed to the renderer.
     * @param  null|Page  $page    Null or an instance of \Grav\Common\Page.
     *
     * @return string              The rendered contents.
     */
    public function render($content, $options = [], $page = null)
    {
        // Set unique identifier based on page content
        $this->id($page->id() ?: time() . md5($content));
        return $this->markdown->text($content);
    }

    /**
     * Normalize content i.e. replace all hashes with their corresponding
     * math formula
     *
     * @param  string $content The content to be processed
     *
     * @return string          The processed content
     */
    public function normalize($content, $type = 'html')
    {
        $hashes = array_keys($this->hashes);
        $text = array_column(array_values($this->hashes), $type);

        // Fast replace hashes with their corresponding math formula
        $content = str_replace($hashes, $text, $content);

        // Return normalized content
        return $content;
    }

    /**
     * Check whether page content was modified or not.
     *
     * @return boolean     true if content was modified and should be
     *                     re-processed (i.e. replacing tokens), false
     *                     otherwise.
     */
    public function modified()
    {
        return (count($this->hashes) > 0) ? true : false;
    }

    /**
     * Gets and sets the identifier for hashing.
     *
     * @param  string $var the identifier
     *
     * @return string      the identifier
     */
    public function id($var = null)
    {
        if ($var !== null) {
            $this->id = $var;
        }
        return $this->id;
    }

    /**
     * Reset MathJax class
     */
    public function reset()
    {
        $this->hashes = [];
    }

    /**
     * Setup the markdown parser to handle LaTeX formula properly.
     *
     * @param  mixed $markdown The markdown parser object
     */
    public function setupMarkdown($markdown)
    {
        /**
         * Markdown blocks
         */

        $delimiters = Grav::instance()['config']->get('plugins.mathjax.delimiters', []);

        // Add Latex block environment to Markdown parser
        $this->markdown = $markdown;
        foreach ($delimiters['block'] as $begin => $end) {
            $markdown->addBlockType($begin[0], 'Latex', true, true, 0);
        }

        $markdown->blockLatex = function($line, $block = null) use ($delimiters)
        {
            if (!$this->enabled()) {
                return;
            }

            foreach ($delimiters['block'] as $begin => $end) {
                if (preg_match('/^(' . preg_quote($begin) . ')[ ]*$/', $line['text'], $matches)) {
                    $block = [
                        'begin' => $begin,
                        'end' => $end,
                        'element' => [
                            'name' => 'p',
                            'attributes' => [
                                'class' => 'mathjax mathjax--block'
                            ],
                            'text' => [],
                        ]
                    ];

                    return $block;
                }
            }
        };

        $markdown->blockLatexContinue = function($line, $block)
        {
            if (isset($block['complete'])) {
                return;
            }

            if (preg_match('/^'. preg_quote($block['end']) . '[ ]*$/', $line['text'])) {
                $block['complete'] = true;
                return $block;
            }

            $block['element']['text'][] = $line['body'];
            return $block;
        };

        $markdown->blockLatexComplete = function($block)
        {
            $text = '\\[' . "\n";
            $text .= implode("\n", $block['element']['text']);
            $text .= '\\]';

            $this->id(time() . md5($text));
            $block['element']['text'] = $text;
            $block['markup'] = $this->hash($block['element'], $text);
            return $block;
        };

        /**
         * Markdown inline
         */

        // Add Latex inline environment to Markdown parser
        foreach ($delimiters['inline'] as $begin => $end) {
            $markdown->addInlineType($begin[0], 'Latex', 0);
        }

        $markdown->inlineLatex = function($excerpt) use ($delimiters)
        {
            if (!$this->enabled()) {
                return;
            }

            foreach ($delimiters['inline'] as $begin => $end) {
                $begin = preg_quote($begin);
                $end = preg_quote($end);

                if (preg_match('/^(' . $begin . ')[ ]*(.+?)[ ]*(' . $end . ')/s', $excerpt['text'], $matches))
                {
                    $text = preg_replace("/[\pZ\pC]+/u", ' ', '\\(' . $matches[2] . '\\)');
                    $block = [
                        'extent' => strlen($matches[0]),
                        'begin' => $matches[1],
                        'end' => $matches[3],
                        'element' => [
                            'name' => 'span',
                            'attributes' => [
                                'class' => 'mathjax mathjax--inline'
                            ],
                            'text' => $text
                        ]
                    ];

                    $this->id(time() . md5($text));
                    $block['element']['text'] = $text;
                    $block['markup'] = $this->hash($block['element'], $text);
                    return $block;
                }
            }
        };
    }

    /**
     * MathJax shortcode
     *
     * @param  Event  $event An event object.
     * @return string        The parsed shortcode.
     */
    public function mathjaxShortcode(Event $event)
    {
        /* @var \Grav\Common\Data\Data $options */
        $options = $event['options'];

        $body = trim($event['body']);
        $type = $options->get('type', 'block');

        if (isset($this->delimiters[$type])) {
            // Wrap text in display math tags
            list($pre, $post) = $this->delimiters[$type]['\\'];
            $body = $pre . $body . $post;

            return $this->render($body, $event['options'], $event['page']);
        }
    }

    /**
     * Hash a given text.
     *
     * Called whenever a tag must be hashed when a function insert an
     * atomic element in the text stream. Passing $text to through this
     * function gives a unique text-token which will be reverted back when
     * calling normalize.
     *
     * @param  string $text The text to be hashed
     * @param  string $type The type (category) the text should be saved
     *
     * @return string       Return a unique text-token which will be
     *                      reverted back when calling normalize.
     */
    protected function hash($block, $text = '')
    {
        static $counter = 0;

        // String that will replace the tag
        $key = implode('::', array('mathjax', $this->id(), ++$counter, 'M'));

        // Render markdown block
        $html = $this->markdown->elementToHtml($block);
        $this->hashes[$key] = [
            'raw' => $text,
            'html' => $html
        ];

        return $key;
    }
}
