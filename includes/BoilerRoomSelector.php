<?php

/**
 * The BoilerRoomSelector is displayed on edit pages above the edit toolbar.  There are
 * two types of selectors:  the three-button AJAX version that load boilerplate content
 * dynamically and the one-button that functions like a standard web form.
 *
 * The three-button selector will appear on any edit page as long the user has Javascript 
 * enabled and the wiki has AJAX enabled.
 *
 * Otherwise, the one-button selector will only appear when editing a new page.
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright © 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomSelector {

  /**
   * This central function handles the rendering of the boiler room selector
   * on edit pages.  It only does so if there is at least one page in the
   * Boilerplate namespace.
  */
  static function renderOutput( &$toolbar ) {
    global $wgUseAjax, $wgTitle;
    $boilerplateTitles = BoilerplateNamespaces::getAllBoilerplateTitles();
    
    $requestedBoilerplateTitle = BoilerplateNamespaces::getRequestedBoilerplateTitle();
    
    $selector = '';
    if ( count( $boilerplateTitles ) > 0 ) {
      if ( $wgUseAjax ) {
        $selector = self::createMsgVars() . 
                    self::getInitialMessage( $requestedBoilerplateTitle, false ) . 
                    '<div id="boilerRoomSelectorContainer"></div>';
        $selector .= self::createAjaxSelectorScript( $boilerplateTitles, 
                                                     $requestedBoilerplateTitle 
                                                   );
        if ( !$wgTitle->exists() ) {
          $selector .= self::createNoscriptSelector( true, 
                                                     $boilerplateTitles,
                                                     $requestedBoilerplateTitle
                                                   );
        }        
      } else {
        if ( !$wgTitle->exists() ) {
          $selector = self::getInitialMessage( $requestedBoilerplateTitle, true );
          $selector .= self::createNoscriptSelector( false, 
                                                    $boilerplateTitles,
                                                    $requestedBoilerplateTitle
                                                  );
        }
      }
    }
    
    $toolbar = $selector . $toolbar;
    return true;
  }
  
  /**
   * Returns a message to display depending on the circumstances.
  */
  static function getInitialMessage( $requestedBoilerplateTitle, $noParagraphIfEmpty ) {
    global $wgTitle;
    
    if ( $wgTitle->exists() && $requestedBoilerplateTitle ) {
      // Not checking for ajax here, as if javascript is disabled on the 
      // client, that's also no ajax. Instead, the javascript will change the
      // message if the ajax selector was loaded.
      return '<br /><p id="boilerRoomMessage">' . wfMsg( 'br-page-exists-noajax' ) . '</p>';
    } else if ( $requestedBoilerplateTitle ) {
      return '<br /><p id="boilerRoomMessage">' . 
        wfMsg( 'br-loaded-on-new-page', $requestedBoilerplateTitle ) . '</p>';
    } else if ( $noParagraphIfEmpty ) {
      return '';  // no message to preload
    } else {
      return '<br /><p id="boilerRoomMessage"></p>';
    }
  }
  
  /**
   * Gets a short script that sets message variables in Javascript
  */
  static function createMsgVars() {
    global $wgTitle;
    
    $output = '<script type="text/javascript">' . "\n";
    $output .= "var wgbrPageExistsAjaxMsg = '"  . wfMsg( 'br-page-exists-ajax' ) . "';\n";
    $output .= "var wgbrPrependMsg = '"  . wfMsg( 'br-prepend' ) . "';\n";
    $output .= "var wgbrAppendMsg = '"  . wfMsg( 'br-append' ) . "';\n";
    $output .= "var wgbrReplaceMsg = '"  . wfMsg( 'br-replace' ) . "';\n";
    
    $output .= "</script>";
    return $output;
  }
  
  /**
   * Creates the three-button selector that uses AJAX to retrieve boilerplate
   * content.
  */
  static function createAjaxSelectorScript( $titles, $requestedTitle ) {
    global $wgResourceModules, $wgbrIncludes, $wgScriptPath, $wgTitle;
    
    $legend = wfMsg( 'br-selector-legend' );
    $prepend = wfMsg( 'br-selector-prepend' );
    $append = wfMsg( 'br-selector-append' );
    $replace = wfMsg( 'br-selector-replace' );
    
    if ( class_exists( 'ResourceLoader', false ) ) {
      $wgResourceModules['ext.ajaxBoilerRoomSelector'] = array(
        'scripts' => $wgbrIncludes . "/ajaxBoilerRoomSelector.js"
      );
      $resourceLoad = "\nmw.loader.load( 'ext.ajaxBoilerRoomSelector' );";
      $extScriptTag = '';  // won't be using this
    } else {
      $resourceLoad = ''; // don't have resource loader, so won't be using this
      $extScriptTag = '<script type="text/javascript" src="' . $wgScriptPath . 
        '/extensions/BoilerRoom/includes/ajaxBoilerRoomSelector.js"></script>' . "\n";
    }
    
    $displayMsg = '';
    if ( $wgTitle->exists() && $requestedTitle ) {
      $displayMsg .= "\n". 'displayPageExistsMsg();';
    } 
    
    $output =<<<ENDSTARTFORM
{$extScriptTag}<script type="text/javascript">{$resourceLoad}{$displayMsg}
var boilerRoomSelectorContainer = document.getElementById("boilerRoomSelectorContainer");
boilerRoomSelectorContainer.innerHTML =
  '<fieldset class="boilerRoomFieldSet">\\n' +
  '<legend>{$legend}</legend>\\n' +
  '<table class="boilerRoomSelector" style="width: 100%;"><tr>\\n' +
  '<td style="padding: 0 2em 0 0; width: 50%;">\\n' +
  '<select id="boilerRoomSelect" size="7" style="width: 100%;">\\n' +
ENDSTARTFORM;

    $output .= self::optionsFromBoilerplateList( $titles, $requestedTitle );
    
    $output .=<<<ENDFORM
  '</select>\\n' +
  '</td><td style="padding: 0 0 0 2em; width: 50%;">\\n' +
  '<button class="boilerRoomButton" style="width: 100%;" ' +
  'onclick="boilerplateFetch( boilerplatePrepend );">{$prepend}</button>\\n' +
  '<button class="boilerRoomButton" style="width: 100%;" ' +
  'onclick="boilerplateFetch( boilerplateAppend );">{$append}</button>\\n' +
  '<button class="boilerRoomButton" style="width: 100%;" ' +
  'onclick="boilerplateFetch( boilerplateReplace );">{$replace}</button>\\n' +
  '</td></tr></table>\\n' +
  '</fieldset>';
</script>
ENDFORM;

    return $output;
  }
  
  /**
   * Creates the one-button noscript selector.  This isn't very different from a
   * BoilerRoom box generated by the <boilerroombox> tag, other than using a select
   * to provide the boilerplate title.
  */
  static function createNoscriptSelector( $wrap, $titles, $requestedTitle ) {
    global $wgScript, $wgTitle;
    
    $title = $wgTitle->getText();
    $label = wfMsg( 'br-load-boilerplate' );
    $legend = wfMsg( 'br-selector-legend' );
    $submit = htmlspecialchars( $wgScript );
    if ( $wrap === true )
      $output = "<noscript>\n";
    else
      $output = "";
      
    $output .=<<<ENDSTARTFORM
<form action="{$submit}">
  <fieldset class="boilerRoomFieldSet">
  <legend>{$legend}</legend>
  <table class="boilerRoomSelector" style="width: 100%;"><tr>
  <td style="padding: 0 2em 0 0; width: 50%;">
  <input type="hidden" name="action" value="edit" />
  <input type="hidden" name="title"  value="{$title}" />
  <select id="boilerRoomSelect" name="boilerplate" size="1" style="width: 100%;">
ENDSTARTFORM;

    $output .= self::optionsFromBoilerplateList( $titles, $requestedTitle );
    
    $output .=<<<ENDFORM
  </select>
  </td><td style="padding: 0 0 0 2em; width: 50%;">
    <input type='submit' class="boilerRoomBoxButton" value="{$label}" />
  </td></tr></table>
  </fieldset>
</form>
ENDFORM;

    if ( $wrap === true )
      $output .= "</noscript>\n";
    else
      $output .= "";
      
    return $output;
  }
  
  /**
   * Converts an array of boilerplate titles into a string containing
   * HTML option elements.
  */
  static function optionsFromBoilerplateList( $titles, $requestedTitle ) {
    $output = '';
  
    foreach ( $titles as $title ) {
      $output .= "  '" . '<option value="Boilerplate:' . $title . '"';
       
      if ( $requestedTitle == "Boilerplate:" . $title )
        $output .= ' selected="selected"';
      
      $output .= '>'. $title . '</option>\n' . "'" . '+';
    }
    
    return $output;
  }
  
  /**
   * Returns the content for a requested boilerplate to a Javascript function
   * via AJAX.
  */
  static function ajaxGetBoilerplateContent( $title ) {
    return BoilerplateNamespaces::getBoilerplateContent( "Boilerplate:" . $title );
  }
}

?>