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
 * @copyright Copyright © 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomBox {
  /**
   * Preloads a boilerplate if one was requested when editing a new page.
  */
  static function preloadBoilerplateOnNewPage( &$textbox, &$title ) {
    $textbox = self::getBoilerplateContent();
    
    return true;
  }
  
  /**
   * Returns the content of the boilerplate identified in the web request, or an 
   * empty string if the boilerplate does not exist or if no boilerplate was requested.
  */
  static function getBoilerplateContent( ) {
    global $wgRequest;
    
    if ( $wgRequest->getText( 'boilerplate' ) ) {
      return BoilerplateNamespaces::getBoilerplateContent( 
        $wgRequest->getText( 'boilerplate' ) );
    } else {
      return "";
    }
  }
  
  /**
   * Registers the {{#boilerroombox}} parser function and the 
   * <boilerroombox> and <brbox> tags with the parser.
  */
  static function parserFunctionAndTagSetup( &$parser ) {
    $parser->setFunctionHook( 'boilerroombox', 
                              'BoilerRoomBox::parserFunctionRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setHook( 'boilerroombox', 'BoilerRoomBox::tagRender' );
    $parser->setHook( 'brbox', 'BoilerRoomBox::tagRender' );
    return true;
  }

  /**
   * Registers the {{#boilerroombox}} and {{#brbox}} magic words with
   * the parser.
  */
  static function parserFunctionMagic( &$magicWords, $langCode ) {
    $magicWords['boilerroombox'] = array( 0, 'boilerroombox', 'brbox' );
    return true;
  }

  /**
   * This function converts the parameters to the parser function into an
   * array form and outputs the completed form as unparsed HTML.
  */
  static function parserFunctionRender( $parser, $frame, $unexpandedParams ) {
    $params = array();
    foreach ( $unexpandedParams as $unexpandedParam ) {
      $param = explode( '=', trim( $frame->expand( $unexpandedParam ) ), 2 );
      if ( count( $param ) == 2 ) {
        $params[$param[0]] = $param[1];
      } else {
        $params[] = $param[0];
      }
    }
    
    return array( self::renderOutput( $params ), 
                  'noparse' => true, 
                  'isHTML' => true 
                );
  }
  
  /**
   * This function converts the contents of the tag into an array of parameters
   * and outputs the completed form as unparsed HTML.
  */
  static function tagRender( $input, array $args, Parser $parser, PPFrame $frame ) {
    $params = array();
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
  */
  static function renderOutput( $params ) {
    global $wgScript;
    
    $submit = htmlspecialchars( $wgScript );

    if ( isset( $params['align'] ) )
      $style = ' style="text-align: ' . $params['align'] . '"'; 
    else
      $style = '';

    $boilerplate = '';
    if ( isset( $params['boilerplate'] ) ) {
      $boilerplate = "\n" . '<input type="hidden" name="boilerplate" value="';
      $boilerplate .= 
        BoilerplateNamespaces::boilerplateTitleFromText( $params['boilerplate'], false );
      $boilerplate .= '" />';
    }
    
    if ( isset( $params['label'] ) )
      $label = $params['label'];
    else
      $label = wfMsg('br-default-box-label');
      
    if ( isset( $params['title'] ) )
      $title = str_replace( '_', ' ', $params['title']);
    else
      $title = "";
      
    if ( isset( $params['width'] ) )
      $width = $params['width'];
    else
      $width = "30";

    $output = <<<ENDFORM
<div class="boilerRoomBox"{$style}>
<form name="boilerRoomBox" action="{$submit}" method="get" class="boilerRoomBoxForm">
<input type="hidden" name="action" value="edit" />{$boilerplate}
<input class="boilerRoomBoxInput" name="title" type="text" value="{$title}" size="{$width}" />
<input type='submit' class="boilerRoomBoxButton" value="{$label}" />
</form></div>
ENDFORM;

  return $output;
  }
}

?>