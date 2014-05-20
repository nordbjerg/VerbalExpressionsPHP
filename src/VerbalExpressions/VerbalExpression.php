<?php namespace VerbalExpressions;

class VerbalExpression {

  private $modifiers;
  private $prefixes;
  private $source;
  private $suffixes;

  /**
   * Escape special characters so we can match them in regular expressions.
   *
   * @param string $value
   * @return string
   */
  private function sanitize($value) {
    return preg_replace_callback('/[^\w]/', function($matches) {
      if($matches[0] == ' ') {
        return ' ';
      }
      return '\\'.$matches[0];
    }, $value);
  }

  /**
   * Add to the source string.
   *
   * @param string $value
   * @return VEx
   */
  public function add($value) {
    $this->source .= $value;

    return $this;
  }

  /**
   * Start of a regular expression.
   *
   * @param bool $enable
   * @return VEx
   */
  public function start($enable = true) {
    $this->prefixes = $enable ? '/' : '';

    return $this;
  }

  /**
   * End of a regular expression.
   *
   * @param bool $enable
   * @return VEx
   */
  public function end($enable = true) {
    $this->suffixes = $enable ? '/' : '';

    return $this;
  }

  /**
   * Return the compiled regular expression
   *
   * @return string
   */
  public function get() {
    return $this->prefixes.$this->source.$this->suffixes.$this->modifiers;
  }

  /**
   * Add exact match to the regular expression.
   *
   * @param string $value
   * @return VEx
   */
  public function then($value) {
    $this->add($this->sanitize($value));

    return $this;
  }

  /**
   * An alias for then.
   *
   * @see VEx::then($value)
   * @param string $value
   * @return VEx
   */
  public function find($value) {
    return $this->then($value);
  }

  /**
   * Add an optional match to the regular expression.
   *
   * @param string $value
   * @return VEx
   */
  public function maybe($value) {
    $this->add('('.$this->sanitize($value).')?');

    return $this;
  }

  /**
   * Add a match for anything to the regular expression.
   *
   * @return VEx
   */
  public function anything() {
    $this->add('(.*)');

    return $this;
  }

  /**
   * Match anything but the given value.
   *
   * @param string $value
   * @return VEx
   */
  public function anythingBut($value) {
    $this->add('([^'.$this->sanitize($value).']*)');

    return $this;
  }

  /**
   * Add a match for a line break to the regular expression.
   *
   * @return VEx
   */
  public function lineBreak() {
    $this->add('(\\n|(\\r\\n))');

    return $this;
  }

  /**
   * Alias for lineBreak
   *
   * @see VEx::lineBreak()
   * @return type
   */
  public function br() {
    return $this->lineBreak();
  }

  /**
   * Add a match for a tab to the regular expression.
   *
   * @return VEx
   */
  public function tab() {
    $this->add('\\t');

    return $this;
  }

  /**
   * Add a match for any word to the regular expression.
   *
   * @return VEx
   */
  public function word() {
    $this->add("\\w+");

    return $this;
  }

  /**
   * Match any of the given characters.
   *
   * @param type $value
   * @return type
   */
  public function anyOf($value) {
    $this->add('['.$this->sanitize($value).']');

    return $this;
  }

  /**
   * Alias for anyOf.
   *
   * @see VEx::anyOf($value)
   * @param string $value
   * @return VEx
   */
  public function any($value) {
    return $this->anyOf($value);
  }

  /**
   * Match a given range of characters.
   *
   * @param mixed $from, ...
   * @param mixed $to, ...
   * @return VEx
   */
  public function range() {
    $value = '[';
    $args = func_get_args();

    for($from = 0; $from < count($args); $from += 2) {
      $to = $from + 1;
      if(count($args) <= $to) break;

      $value .= $this->sanitize($args[$from]);
      $value .= '-';
      $value .= $this->sanitize($args[$to]);
    }

    $value .= ']';

    $this->add($value);

    return $this;
  }

  /**
   * Add a global modifier to the regular expression.
   *
   * @param string $modifier
   * @return VEx
   */
  public function addModifier($modifier) {
    if(strstr($this->modifiers, $modifier) === false) {
      $this->modifiers .= $modifier;
    }

    return $this;
  }

  /**
   * Remove a global modifier from the regular expression.
   *
   * @param string $modifier
   * @return VEx
   */
  public function removeModifier($modifier) {
    $this->modifiers = str_replace($modifier, '', $this->modifiers);

    return $this;
  }

  /**
   * Add or remove the case insensitive global modifier to the regular expression.
   *
   * @param bool $enable Adds the modifier if true, removes it if false.
   * @return VEx
   */
  public function withAnyCase($enable = true) {
    if($enable) {
      $this->addModifier('i');
    } else {
      $this->removeModifier('i');
    }

    return $this;
  }

  /**
   * An alias for withAnyCase
   *
   * @see VEx::withAnyCase([$enable])
   * @param bool $enable
   * @return VEx
   */
  public function caseInsensitive($enable = true) {
    return $this->withAnyCase($enable);
  }

  /**
   * Match multiple of the given value
   *
   * @param string $value
   * @return VEx
   */
  public function multiple($value) {
    $value = $this->sanitize($value);

    switch(substr($value, -1)) {
      case '*':
      case '+':
        break;
      default:
        $value .= '+';
    }

    $this->add($value);

    return $this;
  }

  /**
   * Add an optional regular expression (aka. an or clause).
   *
   * @param string $value
   * @return VEx
   */
  public function or_($value) {
        if(strstr($this->prefixes, '(') === false) $this->prefixes .= '(';
        if(strstr($this->suffixes, ')') === false) $this->suffixes = ')'.$this->suffixes;

    $this->add(')|(');

    return $this;
  }

  /**
   * Match the compiled regular expression against a given value.
   *
   * @see preg_match
   * @param string $str
   * @return bool
   */
  public function match($str) {
    return preg_match($this->get(), $str);
  }

  /**
   * Replace the given value by matching it against the compiled regular expression.
   *
   * @see preg_replace
   * @param string $replacement
   * @param string $str
   * @return string
   */
  public function replace($replacement, $str) {
    return preg_replace($this->get(), $replacement, $str);
  }

  /**
   * Split the given value by matching it against the compiled regular expression.
   *
   * @param string $str
   * @return array
   */
  public function split($str) {
    return preg_split($this->get(), $str);
  }

  public static function __callStatic($method, $parameters)
  {
      return call_user_func_array(array(new static, $method), $parameters);
  }

}
