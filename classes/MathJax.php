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

use Grav\Common\GravTrait;
use RocketTheme\Toolbox\Event\Event;

/**
 * MathJax
 *
 * Helper class to include math formulas in your web pages, either using
 * TeX and LaTeX notation, and/or as MathML.
 */
class MathJax
{
  /**
   * @var MathJax
   */
  use GravTrait;

  /** ---------------------------
   * Private/protected properties
   * ----------------------------
   */

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
  protected $hashes;

  /** -------------
   * Public methods
   * --------------
   */

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

    // Setup tags to parse
    $tags = [
      'block' => ['\[', '\]'],
      'inline' => ['\(', '\)']
    ];

    if (isset($tags[$type])) {
      // Wrap text in display math tags
      list($pre, $post) = $tags[$type];
      $body = $pre . $body . $post;

      return $this->render($body, $event['options'], $event['page']);
    }
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

    // Reset class hashes before processing
    // $this->reset();

    $regex = [];
    // Wrap any text between $ ... $ or $$ ... $$ in display math tags.
    $regex['latex-block'] = '~(?<!\\\\)(\$\$)(.+?)\1~msx';
    $regex['latex-inline'] = '~(?<!\\\\)(\$)(.+?)\1~msx';

    // Wrap any text between \[ and \] in display math tags.
    $regex['block'] = '~
      ^\\\\         # line starts with a single backslash (double escaping)
      \[            # followed by a square bracket
      (.+)          # then the actual LaTeX code
      \\\\          # followed by another backslash
      \]            # and closing bracket
      \s*$          # and maybe some whitespace before the end of the line
      ~msxUX';

    // Wrap any text between \( and \) in display math tags.
    $regex['inline'] = '~
      \\\\          # line starts with a single backslash (double escaping)
      \(            # followed by a left parenthesis
      (.+)          # then the actual LaTeX code
      \\\\          # followed by another backslash
      \)            # and a right parenthesis
      ~msxUX';

    // Replace all math formulas by a (unique) hash
    foreach ($regex as $key => $re) {
      $content = preg_replace_callback($re, function($matches) use ($key) {
        return $this->hash(trim($matches[0]), $key);
      }, $content);
    }

    return $content;
  }

  /**
   * Normalize content i.e. replace all hashes with their corresponding
   * math formula
   *
   * @param  string $content The content to be processed
   *
   * @return string          The processed content
   */
  public function normalize($content)
  {
    $hashes = array_keys($this->hashes);
    $text = array_values($this->hashes);

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

  /** -------------------------------
   * Private/protected helper methods
   * --------------------------------
   */

  /**
   * Reset MathJax class
   */
  protected function reset()
  {
    $this->hashes = [];
  }

  /**
   * Hash a given text.
   *
   * Called whenever a tag must be hashed when a function insert an
   * atomic element in the text stream. Passing $text to through this
   * function gives a unique text-token which will be reverted back when
   * calling unhash.
   *
   * @param  string $text The text to be hashed
   * @param  string $type The type (category) the text should be saved
   *
   * @return string       Return a unique text-token which will be
   *                      reverted back when calling unhash.
   */
  protected function hash($text, $type = '')
  {
    static $counter = 0;

    // Swap back any tag hash found in $text so we do not have to `unhash`
    // multiple times at the end.
    $text = $this->unhash($text);

    // Then hash the block
    $key = implode('::', array('mathjax', $type, $this->id, ++$counter, 'M'));

    // Wrap and add class to formula
    $inline = (strpos($type, 'inline') !== false) ? 'inline' : 'block';
    $text = '<span class="mathjax '.$inline.'">'.$text.'</span>';

    $this->hashes[$key] = $text;

    // String that will replace the tag
    return $key;
  }

  /**
   * Swap back in all the tags hashed by hash.
   *
   * @param  string $text The text to be un-hashed
   *
   * @return string       A text containing no hash inside
   */
  protected function unhash($text)
  {
    $pattern = '~mathjax::(.+)::([0-9a-z]+)::([0-9]+)::M~i';
    $text = preg_replace_callback($pattern, function($matches) {
      return $this->hashes[$matches[0]];
    }, $text);

    return $text;
  }
}
