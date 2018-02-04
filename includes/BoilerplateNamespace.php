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
   * Holds the singleton instance.
   * @var BoilerplateNamespace
   */
  private static $mInstance = null;
  
  /**
   * Holds the request context.
   * @var RequestContext
   */
  private $mContext = null;
  
  /**
   * Acquires the request context.
   */
  private function __construct() {
    $this->mContext = RequestContext::getMain();
  }
  
  /**
   * Creates the singleton instance if it doesn;t exist and returns it either way.
   * @return BoilerplateNamespace The singleton instance.
   */
  public static function getInstance() {
    if ( self::$mInstance === null ) {
      self::$mInstance = new self();
    }
    
    return self::$mInstance;
  }
  
  /**
   * Sets up the Boilerplate namespace when called on the CanonicalNamespaces hook.
   * @param Array Array of namespace names by index.
   * @global int $wgbrNamespaceIndex The index for the Boilerplate namespace.
   */
  public static function initialize( &$namespaces ) {
    global $wgbrNamespaceIndex;
    
    $namespaces[NS_BOILERPLATE] = "Boilerplate";
    $namespaces[NS_BOILERPLATE_TALK] = "Boilerplate_talk";
  }
  
  /**
   * If there's a boilerplate requested on this page load (in the boilerplate argument in
   * the query string), this determines the title.  It's designed to default all titles
   * without an explicitly declared namespace to the Boilerplate namespace.  If there is
   * no boilerplate requested or the title doesn't exist, it returns false.
   * @return Title The boilerplate title from the web request.
   */
  public function getRequestedBoilerplateTitle( ) {
    return $this->boilerplateTitleFromText( $this->mContext->getRequest()->getText( 'boilerplate' ) );
  }
  
  /**
   * Creates an appropriate boilerplate title from text and returns if it exists, or
   * if $onlyIfExists is false, it returns any valid title constructed from the text,
   * defaulting to the Boilerplate namespace if none is provided.
   * @param string $text The title in string form.
   * @param bool $onlyIfExists If true, returns a title only if that page exists.
   * @return Title The title or null.
   */
  public function boilerplateTitleFromText( $text, $onlyIfExists = true ) {
    if ( $text ) {
      $title = Title::newFromText( $text, NS_BOILERPLATE );
      if ( isset( $title ) && ( !$onlyIfExists || $title->exists() ) ) {
        return $title;
      } else {
        return null;
      }
    } else {
      return null;  // no boilerplate requested
    }
  }
  
  /**
   * This function returns all of the boilerplates currently available in the
   * Boilerplate namespace.  They are returned without the namespace.
   * @return Array All titles of pages in the Boilerplate namespace, sans namespace.
  */
  public function getAllBoilerplateTitles() {
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
   * Returns a BolierplatePage instance for the content of the boilerplate indicated by the title text passed in, or
   * null if the boilerplate does not exist or if no boilerplate was requested.
   * @param string $boilerplateTitleText The title of the boilerplate, in text form. Defaults to Boilerplate namespace.
   * @param int $type The type of boilerplate content to return.
   * @return BoilerplatePage The content of the boilerplate in a BoilerplatePage or null if it doesn't exist..
  */
  public function getBoilerplatePage( $boilerplateTitleText ) {
    if ( $boilerplateTitleText ) {
      $boilerplateTitle = Title::newFromText( $boilerplateTitleText, NS_BOILERPLATE );

      if ( isset( $boilerplateTitle ) && $boilerplateTitle->getNamespace() === NS_MEDIAWIKI ) {
        return new BoilerplatePage( wfMessage( $boilerplateTitle->getText() )->text() );
      } if ( isset( $boilerplateTitle ) && $boilerplateTitle->exists() ) {
        $boilerplateWikiPage = WikiPage::newFromId( $boilerplateTitle->getArticleId() );
        return new BoilerplatePage( $boilerplateWikiPage->getContent( Revision::RAW )->getNativeData() );
      } else {
        return null;
      }
    } else {
      return null;
    }
  }
  
  /**
   * Preloads a boilerplate if one was requested when editing a new page.
   * @param string $textbox Content to prefill textbox with.
   * @param Title $title The title of the new page. Value is ignored.
   * @return bool true to indicate no problems.
   */
  public function preloadOnNewPage( &$textbox, &$title ) {
    $boilerplateTitleText = $this->mContext->getRequest()->getText( 'boilerplate' );
    if ( $boilerplateTitleText ) {
      $text = $this->getBoilerplatePage( $boilerplateTitleText )->getCombinedBoilerplateContent();
      if ( $text ) {
        if ( $textbox ) {
          $textbox = $textbox . "\n\n" . $text;
        } else {
          $textbox = $text;
        }
      }
    }
    
    return true;
  }
}