<?php

/**
 * The BoilerRoomSelector is displayed on edit pages above the edit toolbar.  There are
 * two types of selectors:  the three-button AJAX version that load boilerplate content
 * dynamically and the one-button that functions like a standard web form.
 *
 * The four-button selector will appear on any edit page as long the user has Javascript 
 * enabled and the wiki has AJAX enabled.
 *
 * Otherwise, the one-button selector will only appear when editing a new page.
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright ? 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomSelector {

  /**
   * This central function handles the rendering of the boiler room selector on edit pages.  It only does so if there 
   * is at least one page in the Boilerplate namespace.
   * @global bool $wgUseAjax true if AJAX is allowed by the MediaWiki configuration.
   * @global Title $wgTitle The title of the current page.
   * @global bool $wgbrUseLargeSelector true to use the large AJAX selector.
   * @param string $toolbar The HTML for the toolbar.
   * @return bool true to indicate no problems occurred.
   */
  static public function renderOutput( &$toolbar ) {
    global $wgUseAjax, $wgTitle, $wgbrUseLargeSelector;
    
    $requestedBoilerplateTitle = BoilerplateNamespace::getRequestedBoilerplateTitle();
    $titles = BoilerplateNamespace::getAllBoilerplateTitles();
    $htmlOptions = self::optionsFromBoilerplateList( $titles,
                                                     BoilerplateNamespace::getRequestedBoilerplateTitle()
                                                   );
    
    $pageExists = $wgTitle->exists();
    $showExistenceMessage = $pageExists && (bool)$requestedBoilerplateTitle;
    
    $message = $ajaxSelector = $noScriptSelector = '';
    if ( $options != "''" ) {
      if ( $wgUseAjax ) {
        $message = self::getInitialMessage( $requestedBoilerplateTitle, false );
        $ajaxSelector =  self::createAjaxSelectorScript( $htmlOptions, $jsonOptions, $showExistenceMessage );
        if ( !$pageExists ) {
          $noScriptSelector .= self::createNoscriptSelector( true, $options );
        }        
      } else {
        if ( !$pageExists ) {
          $message = self::getInitialMessage( $requestedBoilerplateTitle, true );
          $noScriptSelector .= self::createNoscriptSelector( false, $options );
        }
      }
    }
    
    $toolbar = $message . $noScriptSelector . str_replace( "</div>", "", $toolbar ) . $ajaxSelector . "</div>";
    
    return true;
  }
  
  /**
   * Returns a message to display depending on the circumstances.
   * @global Title $wgTitle The title of the current page.
   * @global bool $wgbrUseLargeSelector True to the use the large AJAX selector.
   * @param Title $requestedBoilerplateTitle The requested boilerplate.
   * @param bool $noParagraphIfEmpty true to suppress providing the message p element if there is no initial message.
   * @return string The HTML for the initial message or an empty string if there is to be none.
   */
  static private function getInitialMessage( $requestedBoilerplateTitle, $noParagraphIfEmpty ) {
    global $wgTitle, $wgbrUseLargeSelector;
    
    if ( $wgbrUseLargeSelector ) {
      $style = "text-align: center; font-weight: bold; clear: both;";
    } else {
      $style = "text-align: right; font-weight: bold; font-size: smaller; clear: both;";
    }
    
    $message = '';
    if ( $wgTitle->exists() && $requestedBoilerplateTitle ) {
      // Not checking for ajax here, as if javascript is disabled on the 
      // client, that's also no ajax. Instead, the javascript will change the
      // message if the ajax selector was loaded.
      $message = wfMessage( 'br-page-exists-noajax' )->text();
    } else if ( $requestedBoilerplateTitle ) {
      $message = wfMessage( 'br-loaded-on-new-page', $requestedBoilerplateTitle->getText() )->text();
    } else if ( !$noParagraphIfEmpty ) {
      $message = wfMessage( 'br-selector-initial' )->text();
    } else {
      return '';  // no message to preload
    }
    
    return Xml::element( 'br' ) .
           Xml::element( 'p', 
                         Array( 'id' => 'boilerRoomMessage',
                                'style' => $style,
                              ),
                         $message,
                         false
                       );
  }
  
  /**
   * Creates the four-button selector that uses AJAX to retrieve boilerplate content.
   * @global bool $wgbrUseLargeSelector True to the use the large AJAX selector.
   * @global string $wgScriptPath The path to the MediaWiki installation base folder.
   * @global OutputPage $wgOUt The output page object to add the static scripts to.
   * @param String $htmlOptions The list of boilerplates that can be selected in HTML format.
   * @param bool $showExistenceMessage true to show the message that the page already exists.
   * @return string The dynamic HTML and Javascript needed for the selector.
   */
  static private function createAjaxSelectorScript( $htmlOptions, $showExistenceMessage ) {
    global $wgbrUseLargeSelector, $wgScriptPath, $wgOut;
          
    $wgOut->addInlineScript("$(document).ready(function(){boilerRoom.addMessages({" .
                            "pageExistsAjaxMsg:'"  . wfMessage( 'br-page-exists-ajax' )->text() . "'," .
                            "prependMsg:'"  . wfMessage( 'br-prepend' )->text() . "'," .
                            "appendMsg:'"  . wfMessage( 'br-append' )->text() . "'," .
                            "replaceMsg:'"  . wfMessage( 'br-replace' )->text() . "'," .
                            "insertMsg:'"  . wfMessage( 'br-insert' )->text() . "'," .
                            "selectorLegend:'" . wfMessage( 'br-selector-legend' )->text() . "'," .
                            "selectorInsert:'" . wfMessage( 'br-selector-insert' )->text() . "'," .
                            "selectorReplace:'" . wfMessage( 'br-selector-replace' )->text() . "'," .
                            "selectorPrepend:'" . wfMessage( 'br-selector-prepend' )->text() . "'," .
                            "selectorAppend:'" . wfMessage( 'br-selector-append' )->text() . "'" .
                            "});boilerRoom.addState({" . 
                            "useLargeSelector:" . ( $wgbrUseLargeSelector ? "true" : "false" ) . "," .
                            "showExistenceMessage:" . ( $showExistenceMessage ? "true" : "false" ) . "," .
                            "standardHtmlList:'" . $htmlOptions . "'," .
                            "});boilerRoom.initializeAjaxSelector();});"
    );
    
    $wgOut->addModuleScripts( 'ext.BoilerRoom.ajaxSelector' );
    
    return Xml::element( 'div', Array( 'id' => 'boilerRoomSelectorContainer' ), '', false );
  }
  
  /**
   * Creates the one-button noscript selector.  This isn't very different from a BoilerRoom box generated by the 
   * <boilerroombox> tag, other than using a select to provide the boilerplate title.
   * @global string $wgScript The path to index.php.
   * @global Title $wgTitle The title of the current page.
   * @param bool $wrap true to wrap in <noscript> tags.
   * @param Array $options The list of boilerplates that can be selected.
  */
  static private function createNoscriptSelector( $wrap, $options ) {
    global $wgScript, $wgTitle;
    
    $title = $wgTitle->getText();
    $label = wfMessage( 'br-load-boilerplate' )->text();
    $legend = wfMessage( 'br-selector-legend' )->text();
    $submit = htmlspecialchars( $wgScript );
    if ( $wrap === true )
      $output = Xml::openElement( 'noscript' );
    else
      $output = "";
    
    $output .= Xml::openElement( 'form', Array( 'action' => $submit ) ) .
               Xml::fieldset( $legend, false, Array( 'class'  => 'boilerRoomFieldSet',
                                                     'style'  => 'clear: both;',
                                                   ) 
                            ) .
               Xml::openElement( 'table', Array( 'class'  => 'boilerRoomSelector',
                                                 'style'  => 'width: 100%;',
                                               ) 
                            ) .
               Xml::openElement( 'tr' ) .
               Xml::openElement( 'td', Array( 'style' => 'padding: 0 2em 0 0; width: 50%;' ) ) .
               Xml::input( 'action', false, 'edit', Array( 'type' => 'hidden' ) ) .
               Xml::input( 'title', false, $title, Array( 'type' => 'hidden' ) ) .
               Xml::openElement( 'select', Array( 'id'      => 'boilerRoomSelect',
                                                  'name'    => 'boilerplate',
                                                  'size'    => '1',
                                                  'style'   => 'width: 100%;'
                                                ) 
                               ) .
               $options .
               Xml::closeElement( 'select' ) .
               Xml::closeElement( 'td' ) .
               Xml::openElement( 'td', Array( 'style' => 'padding: 0 0 0 2em; width: 50%;' ) ) .
               Xml::submitButton( $label, Array( 'class' => 'boilerRoomBoxButton' ) ) .
               Xml::closeElement( 'td' ) .
               Xml::closeElement( 'tr' ) .
               Xml::closeElement( 'table' ) .
               Xml::closeElement( 'fieldset') .
               Xml::closeElement( 'form' );

    if ( $wrap === true )
      $output .= Xml::closeElement( 'noscript' );
      
    return $output;
  }
  
  /**
   * Converts an array of boilerplate titles into a string containing HTML option elements.
   * @param Array $titles The list of boilerplate titles to serve as options, sans namespace.
   * @param string The title, in string form, of the requested boilerplate, with namespace.
   */
  static private function optionsFromBoilerplateList( $titles, $requestedTitle ) {
    $output = "";
  
    foreach ( $titles as $title ) {
      $attribs = Array();
      $attribs['value'] = "Boilerplate:" . $title;
       
      if ( $requestedTitle == "Boilerplate:" . $title )
        $attribs['selected'] = 'selected';
      
      $output .= Xml::openElement( 'option', $attribs ) .
                 $title .
                 Xml::closeElement( 'option' );
    }
    
    return $output;
  }
}

?>