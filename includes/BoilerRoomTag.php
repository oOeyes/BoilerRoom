<?php

/**
 * <boilerroom> is used to delimit the actual text of a boilerplate in a boilerplate
 * article.  To avoid the need for any text escaping, it functions in a non-compliant way.
 *
 * Everything between the first <boilerroom> on a page to the last </boilerroom> is considered
 * to be inside the tag, regardless of if standard XML or HTML tag parser would consider that
 * to be all inside the tag.
 *
 * Additionally, the actual content begins immediately after the first newline after the first
 * <boilerroom> tag nd ends immediately before the newline before the last </boilerroom> tag.
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright © 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomTag {
  const OPENING_TAG_LENGTH = 12;
  const CLOSING_TAG_LENGTH = 13;
  
  /**
   * private
   */
  var $mBefore = '';
  var $mContent = '';
  var $mAfter = '';
  var $mPreprocessTemplate;
  var $mTemplateCallback;
  
  /**
   * Adds the parser hook for the <boilerroom> tag, though this is done just to
   * register the tag with MediaWiki, as the tag is actually processed in the
   * ParserBeforeStrip hook and in the template callback set up by this function.
  */
  function initialize( Parser &$parser ) {
    $parser->setHook( 'boilerroom', array( $this, 'processTag') );
    $this->mPreprocessTemplate = array( $this, 'processTemplate' );
    return true;
  }
  
  /**
   * Returns the boilerplate content within the <boilerroom> tag.
  */
  function getContent( ) {
    return $this->mContent;
  }
  
  /**
   * Is hooked to ParserBeforeStrip to catch the content of the page.  While it also
   * catches other things, the effect of this extension's processing is harmless.
  */
  function processPage( &$parser, &$text, &$strip_state ) {
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
  */
  function processTemplate( $title, $parser=false ) {
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
  */  
  function processContent( $content ) {
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
   * This function divides the text into header, content, and footer as divided by the
   * <boilerroom> tag, assigning them to the private member variables.
  */
  function trisectText( $text ) {
    $startOpeningTag = strpos( $text, '<boilerroom>' );  // find first opening tag
    $endClosingTag = strrpos( $text, '</boilerroom>' );  // find last closing tag
    
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
   * <boilerroom> tag, this should only be called for malformatted situations.
  */
  function processTag( $input, array $args, Parser $parser, PPFrame $frame ) {
    return "BoilerRoom error: possible missing opening tag, missing closing tag, or malformatted tag.";
  }
}

?>