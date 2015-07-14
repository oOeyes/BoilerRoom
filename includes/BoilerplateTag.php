<?php

/**
 * <boilerroom> is used to delimit the actual text of a boilerplate in a boilerplate
 * article.  To avoid the need for any text escaping, it functions in a non-compliant way.
 *
 * Everything between the first <boilerplate> on a page to the last </boilerplate> is considered
 * to be inside the tag, regardless of if standard XML or HTML tag parser would consider that
 * to be all inside the tag.
 *
 * Additionally, the actual content begins immediately after the first newline after the first
 * <boilerplate> tag nd ends immediately before the newline before the last </boilerplate> tag.
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerplateTag {
  /**
   * The length in characters of the opening <boilerplate> tag.
   */
  const OPENING_TAG_LENGTH = 13;
  
  /**
   * The length in characters of the closing <boilerplate> tag.
   */
  const CLOSING_TAG_LENGTH = 14;
  
  /**
   * The content before the opening <boilerplate> tag.
   * @var string
   */
  private $mBefore = '';
  
  /**
   * The content of the boilerplate tag itself.
   * @var string
   */
  private $mContent = '';
  
  /**
   * The content after the closing <boilerplate> tag.
   * @var string
   */
  private $mAfter = '';
  
  /**
   * The callback used to process the <boilerplate> tag within transcluded templates.
   * @var Array|string
   */
  private $mPreprocessTemplate;
  
  /**
   * The original callback used for processing transcluded templates. Called after processing the <boilerplate> tag
   * within them.
   * @var Array|string
   */
  private $mTemplateCallback;
  
  /**
   * Adds the parser hook for the <boilerplate> tag, though this is done just to
   * register the tag with MediaWiki, as the tag is actually processed in the
   * ParserBeforeStrip hook and in the template callback set up by this function.
   * @param Parser $parser The parser object
   * @return bool true to indicate there were no problems.
   */
  public function initialize( Parser &$parser ) {
    $parser->setHook( 'boilerplate', Array( $this, 'processTag') );
    $this->mPreprocessTemplate = Array( $this, 'processTemplate' );
    return true;
  }
  
  /**
   * Returns the boilerplate content within the <boilerplate> tag.
   * @return string The content within the <boilerplate> tag according to its processing rules.
   */
  public function getContent( ) {
    return $this->mContent;
  }
  
  /**
   * Is hooked to ParserBeforeStrip to catch the content of the page.  While it also
   * catches other things, the effect of this extension's processing is harmless.
   * @param Parser $parser The parser object.
   * @param string $text The text currently being parsed.
   * @param StripState $strip_state Ignored.
   * @return bool true to indicate there were no problems.
   */
  public function processPage( &$parser, &$text, &$strip_state ) {
    if ( $this->mPreprocessTemplate !== $parser->getOptions()->getTemplateCallback() )
      $this->mTemplateCallback = $parser->getOptions()->getTemplateCallback();
    if ( isset( $this->mTemplateCallback ) )
      $parser->getOptions()->setTemplateCallback( $this->mPreprocessTemplate );
      
    $text = $this->processContent( $text );
    return true;
  }
  
  /**
   * There is no hook suitable for getting the raw text of templates being transcluded,
   * but we instead provide this function as a callback the parser uses anytime it
   * retrieves a template, and it processes it after retrieval.
   * @param Title $title The title of the page.
   * @param Parser $parser The parser object, or false.
   * @return The content of the template with the <boilerplate> tags processed if there.
   */
  public function processTemplate( $title, $parser = false ) {
    // Not actually overriding, just adding an extra step
    $template = call_user_func( $this->mTemplateCallback, $title, $parser );
    
    $template['text'] = $this->processContent( $template['text'] );

    return $template;
  }
  
  /**
   * <boilerroom> needs different behavior than most tags.  We want to return everything
   * between the *first* opening tag and the *last* closing tag, ignoring any in between.
   * Thus, preprocessing escapes the content in <nowiki> and needs to be called before any
   * parsing is done.
   * @param string $content Content of a page or transcluded template.
   * @return The content with anything within the <boilerplate> tags properly escaped with <nowiki>.
   */  
  private function processContent( $content ) {
    $this->trisectText( $content );
    
    $text = "";
    if ( $this->mBefore )
      $text = $this->mBefore;
    if ( $this->mContent )
      $text .= "\n <nowiki>" . htmlspecialchars( $this->mContent ) . "</nowiki>\n";
    if ( $this->mAfter )
      $text .= $this->mAfter;
    
    return $text;
  }
  
  /**
   * This function divides the text into header, content, and footer as divided by the <boilerplate> tag.
   * @param $text The text to trisect.
   */
  public function trisectText( $text ) {
    $startOpeningTag = strpos( $text, '<boilerplate>' );  // find first opening tag
    $endClosingTag = strrpos( $text, '</boilerplate>' );  // find last closing tag
    
    if ( ($startOpeningTag !== false && $endClosingTag !== false) &&
        $startOpeningTag < $endClosingTag ) {
      $endClosingTag += self::CLOSING_TAG_LENGTH; // need the end position of the tag
      $this->mBefore = substr( $text, 0, $startOpeningTag );
      $this->mAfter = substr( $text, $endClosingTag );
      
      // for better display in the raw code, content begins the line after the 
      // opening tag and terminates at the end of the line before the closing tag 
      $startContent = strpos( $text, "\n", $startOpeningTag + self::OPENING_TAG_LENGTH ) + 1;
      $endContent = strrpos( $text, "\n", 
        -strlen( $text ) + $endClosingTag - self::CLOSING_TAG_LENGTH );
      
      if ( $startContent !== false && $endContent !== false && $startContent < $endContent ) {
        // look for the windows-style line break, just in case
        if ( substr( $text, $endContent - 1, 1 ) == "\r" )
          $endContent--;
        $this->mContent = substr( $text, $startContent, $endContent - $startContent );
      }
    } else {
      $this->mBefore = $text;
      $this->mContent = '';
      $this->mAfter = '';
    }
  }
  
  /**
   * This is the actual tag hook, but due to the unique, non-compliant nature of the 
   * <boilerplate> tag, this should only be called for malformatted situations.
   * @param string $inout The input content. Ignored.
   * @param Array $args The attributes. Ignored.
   * @param Parser $parser The Parser object. Ignored.
   * @param PPFrame $frame The parser frame object. Ignored.
   */
  function processTag( $input, array $args, Parser $parser, PPFrame $frame ) {
    return "BoilerRoom error: possible missing opening tag, missing closing tag, or malformatted tag.";
  }
}

?>