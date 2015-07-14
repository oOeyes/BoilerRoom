<?php

/**
 * This creates the Boilerplate and Boilerplate talk namespaces and includes a number
 * of utility functions related to the Boilerplate namespace.
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright © 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerplateNamespaces {
  
  /**
   * Sets up the Boilerplate namespace.
  */
  static function initialize( ) {
    global $wgbrNamespaceIndex, $wgExtraNamespaces;
    
    define("NS_BOILERPLATE", $wgbrNamespaceIndex);
    define("NS_BOILERPLATE_TALK", $wgbrNamespaceIndex + 1);
    
    $wgExtraNamespaces[NS_BOILERPLATE] = "Boilerplate";
    $wgExtraNamespaces[NS_BOILERPLATE_TALK] = "Boilerplate_talk";
  }
  
  /**
   * If there's a boilerplate requested on this page load (in the boilerplate argument in
   * the query string), this determines the title.  It's designed to default all titles
   * without an explicitly declared namespace to the Boilerplate namespace.  If there is
   * no boilerplate requested or the title doesn't exist, it returns false.
  */
  static function getRequestedBoilerplateTitle( ) {
    global $wgRequest;
    
    return self::boilerplateTitleFromText( $wgRequest->getText( 'boilerplate' ) );
  }
  
  /**
   * Creates an appropriate boilerplate title from text and returns if it exists, or
   * if $onlyIfExists is false, it returns any valid title constructed from the text,
   * defaulting to the Boilerplate namespace if none is provided.
  */
  static function boilerplateTitleFromText( $text, $onlyIfExists = true ) {
    if ( $text ) {
      $title = Title::newFromText( $text, NS_BOILERPLATE );
      if ( isset( $title ) && ( $title->exists() || !$onlyIfExists ) )
        return $title;
      else
        return false;
    } else {
      return false;  // no boilerplate requested
    }
  }
  
  /**
   * This function returns all of the boilerplates currently available in the
   * Boilerplate namespace.  They are returned without the namespace.
  */
  static function getAllBoilerplateTitles() {
    $dbr = wfGetDB( DB_SLAVE );

    $results = $dbr->select( 
                  'page',
                  'page_title',
                  'page_namespace = ' . NS_BOILERPLATE . ' AND page_is_redirect = 0',
                  __METHOD__,
                  array( 'ORDER BY' => "page_title ASC" )
                );
    
    $boilerplates = array();
    foreach ( $results as $result ) {
      $boilerplates[] = str_replace( '_', ' ', $result->page_title );
    }
    return $boilerplates;
  }
  
  /**
   * Returns the content of the boilerplate indicated by the title text passed in, or an
   * empty string if the boilerplate does not exist or if no boilerplate was requested.
  */
  static function getBoilerplateContent( $boilerplateTitleText ) {
    global $wgRequest;

    if ( $boilerplateTitleText ) {
      $boilerplateTitle = Title::newFromText( $boilerplateTitleText );

      if ( isset( $boilerplateTitle ) && $boilerplateTitle->exists() ) {
        $boilerplate = Article::newFromId( $boilerplateTitle->getArticleId() );
        $boilerplateParser = new BoilerRoomTag;
        $boilerplateParser->trisectText( $boilerplate->getRawText() );
        return $boilerplateParser->getContent();
      } else {
        return "";
      }
    } else {
      return "";
    }
  }
}

?>