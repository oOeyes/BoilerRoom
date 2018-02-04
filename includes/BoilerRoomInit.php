<?php

/**
 * Performs initialization steps not possible to complete in extension.json.
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomInit {
  
  /**
   * Callback in extension.json to wrap up extension initialization
   * @global $wgHooks Array Array containing the functions to call on MediaWiki hooks.
   * @global $wgbrNamespaceIndex int The namespace index for the Boilerplate namespace.
   */
  public static function onRegistration() {  
    global $wgHooks, $wgbrNamespaceIndex;
    
    // Set here to allow the API access to the constants.
    define("NS_BOILERPLATE", $wgbrNamespaceIndex);
    define("NS_BOILERPLATE_TALK", $wgbrNamespaceIndex + 1);
    
    $wgHooks['ParserFirstCallInit'][] = Array( BoilerplateTag::getInstance(), 'initialize' );
    $wgHooks['ParserBeforeStrip'][] = Array( BoilerplateTag::getInstance(), 'processPage' );
    $wgHooks['EditFormPreloadText'][] = Array ( BoilerplateNamespace::getInstance(), 'preloadOnNewPage' );

    $wgHooks['ParserFirstCallInit'][] = Array( BoilerRoomBoxHooks::getInstance(), 'parserFunctionAndTagSetup' );

    $wgHooks['EditPage::showEditForm:initial'][] = Array( BoilerRoomSelector::getInstance(), 'renderOutput' );
  }
}