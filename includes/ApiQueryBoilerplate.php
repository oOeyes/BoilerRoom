<?php

/**
 * This is a simple top-level API module that gets the boilerplate content of tne current revision of a given page.
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class ApiQueryBoilerplate extends APIBase {
  
  /**
   * Executes the API request for given boilerplate content.
   */
  public function execute( ) {
    $this->params = $this->extractRequestParams();
    $result = $this->getResult();
    $bpns = BoilerplateNamespace::getInstance();
    
    if ( isset( $this->params['title'] ) ) {
      $boilerplatePage = $bpns->getBoilerplatePage( $this->params['title'] );
      if ( $boilerplatePage !== null ) {
        $content = $boilerplatePage->getBoilerplateContent();

        if ( $content[BoilerplatePage::standardContent] !== null ) {
          $result->addValue( Array( 'boilerplate' ), '*', $content[BoilerplatePage::standardContent] );
        }
        if ( $content[BoilerplatePage::openingContent] !== null ) {
          $result->addValue( Array( 'openboilerplate' ), '*', $content[BoilerplatePage::openingContent] );
        }
        if ( $content[BoilerplatePage::closingContent] !== null ) {
          $result->addValue( Array( 'closeboilerplate' ), '*', $content[BoilerplatePage::closingContent] );
        }
      }
    }
    
    $this->getMain()->setCacheMode( 'public' );
  }
  
  /**
   * Returns the name of the allowed parameter.
   * @return Array Returns the name of the allowed parameter.
   */
  public function getAllowedParams( ) {
    return Array( 'title' => Array( ) );
  }
  
  /**
   * Returns a description of the only allowed parameter.
   * @return Array A description of the only allowed parameter.
   */
  public function getParamDescription( ) {
    return Array( 'title' => Array ( 'The title of the page to get boilerplate content from.',
                                     'Assumes Boilerplate namespace if none is specified.'
                                   ) 
                );
  }
  
  /**
   * Returns a description of the module.
   * @return Array A description of this module.
   */
  public function getDescription( ) {
    return Array( 'Gets boilerplate content from pages using <boilerplate>, <openboilerplate>, and/or ' .
                  '<closeboilerplate>',
                  'Simply returns an empty value for pages not using the tag or for non-existant pages.'
                );
  }
  
  /**
   * Returns a version string. Not consistent with other API modules since I'm not yet using SVN.
   * @return string A version string.
   */
  public function getVersion( ) {
    return __CLASS__ . ': BoilerRoom 1.3.4';
  }
}
