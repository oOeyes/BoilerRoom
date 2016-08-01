<?php

/**
 * This class implements the Boilerplate special page.
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialBoilerplate extends SpecialPage {
  
  /**
   * Initializes the special page.
  */
  function __construct() {
    parent::__construct( 'Boilerplate' );
  }
 
  /**
   * Processes a request for Special:Boilerplate.
   * @param string $par 
  */
  function execute( $par ) {
    $out = $this->getOutput();
    
    $this->setHeaders();
    
    $out->addHTML( Xml::element( 'h2', null, $this->msg( 'br-create-boilerplates' )->escaped() ) );
    
    $out->addHTML( Xml::element( 'h3', null, $this->msg( 'br-create-std-bp' )->escaped() ) );
    $standardBPTitle = $this->chooseBoilerplate( 'br-meta-std-bp-page', 'br-meta-std-bp' );
    $standardBRBox = new BoilerRoomBox( "left", $standardBPTitle, "Create standard boilerplate", "Boilerplate:", 30 );
    $out->addHTML( $standardBRBox->render() );
    $out->addHTML( Xml::element( 'p', null, $this->msg( 'br-create-std-bp-desc' )->parse() ) );
    
    $out->addHTML( Xml::element( 'h3', null, $this->msg( 'br-create-wrap-bp' )->escaped() ) );
    $wrappingBPTitle = $this->chooseBoilerplate( 'br-meta-wrap-bp-page', 'br-meta-wrap-bp' );
    $wrappingBRBox = new BoilerRoomBox( "left", $wrappingBPTitle, "Create wrapping boilerplate", "Boilerplate:", 30 );
    $out->addHTML( $wrappingBRBox->render() );
    $out->addHTML( Xml::element( 'p', null, $this->msg( 'br-create-wrap-bp-desc' )->parse() ) );
    
    $out->addHTML( Xml::element( 'h2', null, $this->msg( 'br-boilerplate-list' )->escaped() ) );
    $titleTexts = BoilerplateNamespace::getInstance()->getAllBoilerplateTitles();
    
    if ( count( $titleTexts ) > 0 ) {
      $ul = Xml::openElement( 'ul' );
      foreach ( $titleTexts as $titleText ) {
        $title = Title::newFromText( $titleText, NS_BOILERPLATE );
        
        if ( $title !== null ) {
          $ul .= Xml::openElement( 'li' );
          $viewAttribs = Array( 'href' => $title->getLinkURL(), 'title' => $title->getPrefixedText() );
          $ul .= Xml::element( 'a', $viewAttribs, $title->getText() );
          $editAttribs = Array( 'href' => $title->getEditURL(), 'title' => $title->getPrefixedText() );
          $ul .= ' (' . Xml::element( 'a', $editAttribs, 'edit' ) . ')';
          $ul .= Xml::closeElement( 'li' );
        }
      }
      $ul .= Xml::closeElement( 'ul' );
      
      $out->addHTML( Xml::element( 'p', null, $this->msg( 'br-boilerplate-list-desc' )->parse() ) );
      $out->addHTML( $ul );
    } else {
      $out->addHTML( Xml::element( 'p', null, $this->msg( 'br-boilerplate-list-none' )->parse() ) );
    }
    $out->addHTML( Xml::element( 'p', null, $this->msg( 'br-boilerplate-list-note' )->parse() ) );
  }
  
  /**
   * Checks the boilerplate identified by title in the given message to see if it has any boilerplate content. If so,
   * it returns the title. Otherwise, it returns the appropriate page title for the fallback. 
   * @param string $titleMessage The message name containing the title for the first-choice boilerplate.
   * @param string $fallbackMessage The message name containing the fallback boilerplate text.
   * @return string The title of the boilerplate to use.
   */
  private function chooseBoilerplate( $titleMessage, $fallbackMessage ) {
    $titleText = trim( $this->msg( $titleMessage )->text() );
    if ( $titleText !== '' ) {
      $boilerplatePage = BoilerplateNamespace::getInstance()->getBoilerplatePage( $titleText );
      if ( $boilerplatePage !== null && $boilerplatePage->hasBoilerplateContent() ) {
        return $titleText;
      } else {
        return "MediaWiki:" . $fallbackMessage;
      }
    } else {
      return "MediaWiki:" . $fallbackMessage;
    }
  }
  
  /**
   * Returns the short description of the special page, used as the page title.
   */
  function getDescription( ) {
    return $this->msg( "boilerplate-special" )->text();
  }
  
  /**
   * Returns the group name this should be listed under.
   */
  function getGroupName( ) {
    return "pagetools";
  }
}