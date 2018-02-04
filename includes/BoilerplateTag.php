<?php

/**
 * This singleton class handles some high-level functions of the <boilerplate>, <openboilerplate>, and 
 * <closeboilerplate> tags such as registration, tying in to MediaWiki hooks, and reporting tag errors. 
 * 
 * Most of the actual tag functions are handled by the BoilerplatePage class, which actually deals with dividing the 
 * boilerplate pages into sections by the tags and rendering their output when boilerplate pages are viewed directly
 * or through a template.
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerplateTag {
  /**
   * Holds the singleton instance.
   * @var BoilerplateNamespace
   */
  private static $mInstance = null;
  
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
   * Creates the singleton instance if it doesn;t exist and returns it either way.
   * @return BoilerplateTag The singleton instance.
   */
  public static function getInstance() {
    if ( self::$mInstance === null ) {
      self::$mInstance = new self();
    }
    
    return self::$mInstance;
  }
  
  /**
   * Adds the parser hook for the <boilerplate>, <openboilerplate>, and <closeboilerplate> tags, though this is done 
   * just to register the tag with MediaWiki, as the tag is actually processed in the ParserBeforeStrip hook and in the 
   * template callback set up by this function.
   * @param Parser $parser The parser object
   * @return bool true to indicate there were no problems.
   */
  public function initialize( Parser &$parser ) {
    $parser->setHook( 'boilerplate', Array( $this, 'processTag') );
    $parser->setHook( 'openboilerplate', Array( $this, 'processTag') );
    $parser->setHook( 'closeboilerplate', Array( $this, 'processTag') );
    $this->mPreprocessTemplate = Array( $this, 'processTemplate' );
    
    return true;
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
    if ( $this->mPreprocessTemplate !== $parser->getOptions()->getTemplateCallback() ) {
      $this->mTemplateCallback = $parser->getOptions()->getTemplateCallback();
    }
    if ( isset( $this->mTemplateCallback ) ) {
      $parser->getOptions()->setTemplateCallback( $this->mPreprocessTemplate );
    }
    
    $text = BoilerplatePage::renderContent( $text );
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
    
    if ( is_string( $template['text'] ) ) {
      $template['text'] = BoilerplatePage::renderContent( $template['text'] );
    }

    return $template;
  }
  
  /**
   * This is the actual tag hook, but due to the unique, non-compliant nature of the these tags, this should only 
   * be called for malformatted situations.
   * @param string $input The input content.
   * @param Array $args The attributes. Ignored.
   * @param Parser $parser The Parser object. Ignored.
   * @param PPFrame $frame The parser frame object. Ignored.
   */
  function processTag( $input, array $args, Parser $parser, PPFrame $frame ) {
    return "<span class=\"error\">" . wfMessage( 'br-tag-error' )->text() . "</span>";
  }
}