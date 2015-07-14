<?php

/**
 * This creates the Boilerplate and Boilerplate talk namespaces and includes a number
 * of utility functions related to the Boilerplate namespace.
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerplateNamespace {
  
  /**
   * Sets up the Boilerplate namespace.
   * @global int $wgbrNamespaceIndex The index for the Boilerplate namespace.
   * @global Array $wgExtraNamespaces The MediaWiki configuration array that configures custom namespaces.
   */
  static public function initialize( ) {
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
   * @global WebRequest $wbRequest The web request object.
   * @return Title The boilerplate title from the web request.
   */
  static public function getRequestedBoilerplateTitle( ) {
    global $wgRequest;
    
    return self::boilerplateTitleFromText( $wgRequest->getText( 'boilerplate' ) );
  }
  
  /**
   * Creates an appropriate boilerplate title from text and returns if it exists, or
   * if $onlyIfExists is false, it returns any valid title constructed from the text,
   * defaulting to the Boilerplate namespace if none is provided.
   * @param string $text The title in string form.
   * @param bool $onlyIfExists If true, returns a title only if that page exists.
   * @return Title|bool The title or false.
   */
  static public function boilerplateTitleFromText( $text, $onlyIfExists = true ) {
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
   * @return Array All titles of pages in the Boilerplate namespace, sans namespace.
  */
  static public function getAllBoilerplateTitles() {
    $dbr = wfGetDB( DB_SLAVE );

    $results = $dbr->select( 'page',
                             'page_title',
                             'page_namespace = ' . NS_BOILERPLATE . ' AND page_is_redirect = 0',
                             __METHOD__,
                             Array( 'ORDER BY' => "page_title ASC" )
                           );
    
    $boilerplates = Array();
    foreach ( $results as $result ) {
      $boilerplates[] = str_replace( '_', ' ', $result->page_title );
    }
    return $boilerplates;
  }
  
  /**
   * Returns the content of the boilerplate indicated by the title text passed in, or an
   * empty string if the boilerplate does not exist or if no boilerplate was requested.
   * @global WebRequest $wgRequest The web request object.
   * @param string $boilerplateTitleText The title of the boilerplate, in text form. Defaults to Boilerplate namespace.
   * @return string The content of the boilerplate or an empty string if it doesn't exist.
  */
  static public function getBoilerplateContent( $boilerplateTitleText ) {
    global $wgRequest;

    if ( $boilerplateTitleText ) {
      $boilerplateTitle = Title::newFromText( $boilerplateTitleText, NS_BOILERPLATE );

      if ( isset( $boilerplateTitle ) && $boilerplateTitle->exists() ) {
        $boilerplate = Article::newFromId( $boilerplateTitle->getArticleId() );
        $boilerplateParser = new BoilerplateTag;
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