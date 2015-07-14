<?php

/**
 * A BoilerRoomBox is a small, simple form on a web page that provides a text box where
 * users can input a title and open that page for editing.  If the page is new, the
 * boilerplate text is automatically.  Otherwise, if the ajax selector is available,
 * the indicated boilerplate is automatically selected.
 *
 * BoilerRoomBoxes are placed on a page using the <boilerroombox> or <brbox> tags, or
 * with the {{boilerroombox}} or {{brbox}} parser functions.
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomBox {
  /**
   * Preloads a boilerplate if one was requested when editing a new page.
   * @param string $textbox Content to prefill textbox with.
   * @param Title $title The title of the new page. Value is ignored.
   * @return bool true to indicate no problems.
   */
  static public function preloadBoilerplateOnNewPage( &$textbox, &$title ) {
    $textbox = self::getBoilerplateContent();
    
    return true;
  }
  
  /**
   * Returns the content of the boilerplate identified in the web request, or an 
   * empty string if the boilerplate does not exist or if no boilerplate was requested.
   * @global $wgRequest The web request object.
   * @return The content of the boilerplate identified in the 'boilerplate' web request param
   */
  static public function getBoilerplateContent( ) {
    global $wgRequest;
    
    if ( $wgRequest->getText( 'boilerplate' ) ) {
      return BoilerplateNamespace::getBoilerplateContent( $wgRequest->getText( 'boilerplate' ) );
    } else {
      return "";
    }
  }
  
  /**
   * Registers the {{#boilerroombox}} parser function and the <boilerroombox> and <brbox> tags with the parser.
   * @param Parser $parser The parser object being initialized.
   * @return bool true to indicate no problems.
   */
  static public function parserFunctionAndTagSetup( &$parser ) {
    $parser->setFunctionHook( 'MAG_BOILERROOMBOX', 
                              'BoilerRoomBox::parserFunctionRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setHook( 'boilerroombox', 'BoilerRoomBox::tagRender' );
    $parser->setHook( 'brbox', 'BoilerRoomBox::tagRender' );
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
  static public function parserFunctionRender( $parser, $frame, $unexpandedParams ) {
    $params = Array();
    foreach ( $unexpandedParams as $unexpandedParam ) {
      $param = explode( '=', trim( $frame->expand( $unexpandedParam ) ), 2 );
      if ( count( $param ) == 2 ) {
        $params[$param[0]] = $param[1];
      } else {
        $params[] = $param[0];
      }
    }
    
    return Array( self::renderOutput( $params ), 
                  'noparse' => true, 
                  'isHTML' => true 
                );
  }
  
  /**
   * This function converts the contents of the tag into an array of parameters
   * and outputs the completed form as unparsed HTML.
   * @param string $inout The input content, not yet processed or split.
   * @param Array $args The attributes. This isn't used.
   * @param Parser $parser The Parser object. Ignored.
   * @param PPFrame $frame The parser frame object. Ignored.
   * @return string The tag's output.
   */
  static public function tagRender( $input, Array $args, Parser $parser, PPFrame $frame ) {
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
    
    return self::renderOutput( $params );
  }
  
  /**
   * This function creates the boilerplate form and returns it.
   * @global $wgScript The path to index.php.
   * @param Array $params An array of the parameter values keyed by parameter name.
   * @return The boiler room box form according to the parameters.
   */
  static private function renderOutput( $params ) {
    global $wgScript;
    
    $submit = htmlspecialchars( $wgScript );

    $style = '';
    if ( isset( $params['align'] ) ) {
      $align = self::validateAlign( $params['align'] );
      if ( $align )
        $style = 'text-align: ' . $align; 
    }      

    $boilerplate = '';
    if ( isset( $params['boilerplate'] ) ) {
      $boilerplate = Xml::input( 'boilerplate', 
                                 false, 
                                 BoilerplateNamespace::boilerplateTitleFromText( $params['boilerplate'], false ), 
                                 Array( 'type' => 'hidden' )
                               );
    }
    
    if ( isset( $params['label'] ) && $params['label'] !== '' )
      $label = $params['label'];
    else
      $label = wfMessage( 'br-default-box-label' )->text();
      
    if ( isset( $params['title'] ) ) {
      $title = str_replace( '_', ' ', $params['title'] );
    } else {
      $title = "";
    }
      
    if ( isset( $params['width'] ) && intval( $params['width'] ) > 0 )
      $width = intval( $params['width'] );
    else
      $width = 30;

    $output = Xml::openElement( 'div', Array( 'class' => 'boilerRoomBox',
                                              'style' => $style,
                                            ) 
                              ) .
              Xml::openElement( 'form', Array( 'name'    => 'boilerRoomBox',
                                               'action'  => $submit,
                                               'method'  => 'get',
                                               'class'   => 'boilerRoomBoxForm',
                                             ) 
                              ) .
              Xml::input( 'action', false, 'edit', Array( 'type' => 'hidden' ) ) .
              $boilerplate .
              Xml::input( 'title', $width, $title, Array( 'type'   => 'text',
                                                          'class'  => 'boilerRoomBoxInput',
                                                        ) 
                        ) .
              Xml::submitButton( $label, Array( 'class' => 'boilerRoomBoxButton' ) ) .
              Xml::closeElement( 'form ') .
              Xml::closeElement( 'div' );

    return $output;
  }
  
  /**
   * Validates an alignment value for the text-align property, returning just the alignment
   * value in lower case if it's valid and false it is invalid.
   * @param $align The user-provided alignment value.
   * @return A safe alignment value if possible, or false.
   */
  static private function validateAlign( $align ) {    
    switch ( trim( strtolower( $align ) ) ) {
      case "center":
        return "center";
      case "left":
        return "left";
      case "right":
        return "right";
      case "justify":
        return "justify";
      case "inherit":
        return "inherit";
      default:
        return false;
    }
  }
}

?>