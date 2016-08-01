<?php

/**
 * BoilerRoom boxes can be placed on a page using the <boilerroombox> or <brbox> tags, or with the {{boilerroombox}} or 
 * {{brbox}} parser functions. This singleton class contains the parser hooks that interface between the MediaWiki 
 * parser and the BoilerRoomBox class.
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomBoxHooks {
  
  /**
   * Holds the singleton instance.
   * @var BoilerRoomBoxHooks
   */
  private static $mInstance = null;
  
  /**
   * Prevents more than one instance.
   */
  private function __construct() { }
  
  /**
   * Creates the singleton instance if it doesn;t exist and returns it either way.
   * @return BoilerRoomBoxHooks The singleton instance.
   */
  public static function getInstance() {
    if ( self::$mInstance === null ) {
      self::$mInstance = new self();
    }
    
    return self::$mInstance;
  }
  
  /**
   * Registers the {{#boilerroombox}} parser function and the <boilerroombox> and <brbox> tags with the parser.
   * @param Parser $parser The parser object being initialized.
   * @return bool true to indicate no problems.
   */
  public function parserFunctionAndTagSetup( &$parser ) {
    $parser->setFunctionHook( 'boilerroombox', 
                              Array( $this, 'parserFunctionRender' ), 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setHook( 'boilerroombox', Array( $this, 'tagRender' ) );
    $parser->setHook( 'brbox', Array( $this, 'tagRender' ) );
    return true;
  }

  /**
   * This function converts the parameters to the parser function into an array form and outputs the completed form 
   * as unparsed HTML.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $unexpandedParams The parameters and values together, not yet exploded or trimmed.
   * @return Array The function output along with relevent parser options.
   */
  public function parserFunctionRender( $parser, $frame, $unexpandedParams ) {
    $params = Array();
    foreach ( $unexpandedParams as $unexpandedParam ) {
      $param = explode( '=', trim( $frame->expand( $unexpandedParam ) ), 2 );
      if ( count( $param ) == 2 ) {
        $params[$param[0]] = $param[1];
      } else {
        $params[] = $param[0];
      }
    }
    
    return Array( BoilerRoomBox::newFromParams( $params )->render(), 
                  'noparse' => true, 
                  'isHTML' => true 
                );
  }
  
  /**
   * This function converts the contents of the tag into an array of parameters
   * and outputs the completed form as unparsed HTML.
   * @param string $input The input content, not yet processed or split.
   * @param Array $args The attributes. This isn't used.
   * @param Parser $parser The Parser object. Ignored.
   * @param PPFrame $frame The parser frame object. Ignored.
   * @return string The tag's output.
   */
  public function tagRender( $input, Array $args, Parser $parser, PPFrame $frame ) {
    $params = Array();
    $lines = explode( "\n", $input );
    foreach ( $lines as $line ) {
      $param = explode( '=', trim( $line ) );
      if ( count( $param ) == 2 ) {
        $params[$param[0]] = $param[1];
      } else if ( $param[0] ) {
        $params[] = $param[0];
      }
    }
    
    return BoilerRoomBox::newFromParams( $params )->render();
  }
}