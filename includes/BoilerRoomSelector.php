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
   * Holds the singleton instance.
   * @var BoilerRoomSelector 
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
   * @return BoilerRoomSelector The singleton instance.
   */
  public static function getInstance() {
    if ( self::$mInstance === null ) {
      self::$mInstance = new self();
    }
    
    return self::$mInstance;
  }

  /**
   * This central function handles the rendering of the boiler room selector on edit pages.
   * @global bool $wgUseAjax true if AJAX is allowed by the MediaWiki configuration.
   * @param EditPage $editPage The edit page object to edit.
   * @param OutputPage $out Tne OutputPage.
   * @return bool true to indicate no problems occurred.
   */
  public function renderOutput( $editPage, $out ) {
    global $wgUseAjax;
    $bpns = BoilerplateNamespace::getInstance();
    
    $titles = $bpns->getAllBoilerplateTitles();
    
    $requestedBoilerplateTitle = $bpns->getRequestedBoilerplateTitle();
    $htmlOptions = $this->optionsFromBoilerplateList( $titles, $requestedBoilerplateTitle );

    $pageExists = $this->mContext->getTitle()->exists();

    $ajaxSelector = $noScriptSection = '';
    if ( $wgUseAjax ) {
      $message = $this->getInitialMessage( $pageExists, $requestedBoilerplateTitle );
      $this->addAjaxSelectorScript( $message, $htmlOptions );      
    }
    
    $noScriptMessage = $this->getNoScriptMessageBlock( $pageExists, $requestedBoilerplateTitle );
    if ( !$pageExists ) {
      $noScriptSection = $this->createNoscriptSelector( $wgUseAjax, $noScriptMessage, $htmlOptions );
    } else if ( $noScriptMessage !== '' ) {
      if ( $wgUseAjax ) {
        $noScriptSection = Xml::element( 'noscript', null, $noScriptMessage );
      } else {
        $noScriptSection = $noScriptMessage;
      }
    }

    $editPage->editFormTextTop .= $noScriptSection;
    
    return true;
  }
  
  /**
   * Potentially returns a message string to display upon page load depending on the given parameters.
   * @param $pageExists True if the currently open page already exists.
   * @param Title $requestedBoilerplateTitle The requested boilerplate.
   * @return string The message or an empty string if there is to be none.
   */
  private function getInitialMessage( $pageExists, $requestedBoilerplateTitle ) {
    if ( $pageExists && $requestedBoilerplateTitle ) {
      return wfMessage( 'br-page-exists-ajax' )->parse();
    } else if ( $requestedBoilerplateTitle ) {
      return wfMessage( 'br-loaded-on-new-page', $requestedBoilerplateTitle->getText() )->parse();
    } else {
      return '';
    }
  }
  
  /**
   * Adds the scripts and vars for the AJAX selector to the output page.
   * @global bool $wgbrUseLargeSelector True to the use the large AJAX selector.
   * @param string $message The message to show upon page load.
   * @param string $htmlOptions The list of boilerplates that can be selected in HTML format.
   * @param bool $showExistenceMessage true to show the message that the page already exists.
   */
  private function addAjaxSelectorScript( $message, $htmlOptions ) {
    global $wgbrUseLargeSelector;
    
    $out = $this->mContext->getOutput();
          
    $out->addJsConfigVars( Array( 'wgbrUseLargeSelector' => $wgbrUseLargeSelector,
                                  'wgbrInitialMessage' => $message,
                                  'wgbrBoilerplateHtmlOptions' => $htmlOptions,
                                ) 
                         );
    
    $out->addModules( 'ext.BoilerRoom.ajaxSelector' );
  }
  
  /**
   * Potentially returns a message block to display depending on the provided circumstances. If there is no message to 
   * display, it returns an empty string.
   * @param $pageExists True if the currently open page already exists.
   * @param Title $requestedBoilerplateTitle The requested boilerplate.
   * @return string The HTML for the noscript message block or an empty string if there is to be none.
   */
  private function getNoScriptMessageBlock( $pageExists, $requestedBoilerplateTitle ) {
    if ( $pageExists && $requestedBoilerplateTitle ) {
      $message = wfMessage( 'br-page-exists-noajax' )->parse();
    } else if ( $requestedBoilerplateTitle ) {
      $message = wfMessage( 'br-loaded-on-new-page', $requestedBoilerplateTitle->getText() )->parse();
    }
    
    if ( isset( $message ) ) {
      return Xml::element( 'p', 
                           Array( 'style' => "text-align: center; font-weight: bold; clear: both;" ),
                           $message,
                           false
                         );
    } else {
      return '';
    }
  }
  
  /**
   * Creates the one-button noscript selector.  This isn't very different from a BoilerRoom box generated by the 
   * <boilerroombox> tag, other than using a select to provide the boilerplate title.
   * @global string $wgScript The path to index.php.
   * @param bool $wrap true to wrap in <noscript> tags.
   * @param string $messageBlock An optional message block in HTML or an empty string.
   * @param Array $options The list of boilerplates that can be selected.
  */
  private function createNoscriptSelector( $wrap, $messageBlock, $options ) {
    global $wgScript;
    
    $title = $this->mContext->getTitle()->getPrefixedText();
    $loadLabel = wfMessage( 'br-load-boilerplate' )->escaped();
    $createLabel = wfMessage( 'br-selector-create' )->escaped();
    $createUrl = Title::newFromText( 'Special:Boilerplate' )->getLinkURL();
    $noBoilerplatesMessage = wfMessage( 'br-no-boilerplates' )->escaped();
    $legend = wfMessage( 'br-selector-legend' )->escaped();
    $submit = htmlspecialchars( $wgScript );
    if ( $wrap === true ) {
      $output = Xml::openElement( 'noscript' );
    } else {
      $output = "";
    }
    
    $output .= $messageBlock .
               Xml::openElement( 'form', Array( 'action' => $submit ) ) .
               Xml::fieldset( $legend, false, Array( 'class'  => 'boilerRoomFieldSet',
                                                     'style'  => 'clear: both;',
                                                   ) 
                            ) .
               Xml::openElement( 'table', Array( 'class'  => 'boilerRoomSelector',
                                                 'style'  => 'width: 100%;',
                                               ) 
                            ) .
               Xml::openElement( 'tr' );
    
    if ( $options !== '' ) {
      $output .= Xml::openElement( 'td', Array( 'style' => 'padding: 0 2em 0 0; width: 40%;' ) ) .
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
                 Xml::openElement( 'td', Array( 'style' => 'padding: 0 0 0 2em; width: 30%;' ) ) .
                 Xml::submitButton( $loadLabel, Array( 'class' => 'boilerRoomBoxButton' ) ) .
                 Xml::closeElement( 'td' ) .
                 Xml::openElement( 'td', Array( 'style' => 'padding: 0 0 0 2em; width: 30%;' ) ) .
                 Xml::element( 'a', Array( 'href' => $createUrl, 'target' => '_blank' ), $createLabel ) .
                 Xml::closeElement( 'td' );
    } else {
      $output .= Xml::openElement( 'td', Array( 'style' => 'padding: 0 0 0 2em; width: 100%; text-align: center;' ) ) .
                 Xml::openElement( 'p', null ) .
                 $noBoilerplatesMessage .
                 Xml::element( 'br' ) .
                 Xml::element( 'a', Array( 'href' => $createUrl ), $createLabel ) .
                 Xml::closeElement( 'p' ) .
                 Xml::closeElement( 'td' );
    }
    $output .= Xml::closeElement( 'tr' ) .
               Xml::closeElement( 'table' ) .
               Xml::closeElement( 'fieldset') .
               Xml::closeElement( 'form' );

    if ( $wrap === true ) {
      $output .= Xml::closeElement( 'noscript' );
    }
      
    return $output;
  }
  
  /**
   * Converts an array of boilerplate titles into a string containing HTML option elements.
   * @param Array $titles The list of boilerplate titles to serve as options, sans namespace.
   * @param string The title, in string form, of the requested boilerplate, with namespace.
   */
  private function optionsFromBoilerplateList( $titles, $requestedTitle ) {
    $output = "";
  
    foreach ( $titles as $title ) {
      $attribs = Array();
      $attribs['value'] = "Boilerplate:" . $title;
       
      if ( $requestedTitle == "Boilerplate:" . $title ) {
        $attribs['selected'] = 'selected';
      }
      
      $output .= Xml::openElement( 'option', $attribs ) .
                 $title .
                 Xml::closeElement( 'option' );
    }
    
    return $output;
  }
}