<?php

/**
 * A BoilerRoom box is a small, simple form on a web page that provides a text box where users can input a title and 
 * open that page for editing.  If the page is new, the boilerplate text is automatically loaded. Otherwise, if the 
 * ajax selector is available, the indicated boilerplate is automatically selected.
 *
 * Users can set these up on wiki pages using the parser functions and tags set up through BoilerRoomBoxHooks, but
 * this is also used for Special:Boilerplate.
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerRoomBox {
  
  /**
   * The requested alignment.
   * @var string
   */
  var $mAlign;
  
  /**
   * The title of the boilerplate to use.
   * @var string
   */
  var $mBoilerplate;
  
  /**
   * The button label to use.
   * @var string
   */
  var $mLabel;
  
  /**
   * The default title to use.
   * @var string
   */
  var $mTitle;
  
  /**
   * The width to use.
   * @var int
   */
  var $mWidth;
  
  /**
   * Creates a BoilerRoomBox from the given parameter array, typically supplied by a parser function or tag rendering
   * function.
   * @param Array $params An array of parameters values indexed by lowercase parameter names.
   * @return BoilerRoomBox A BoilerRoomBox instance generated from these values.
   */
  static public function newFromParams( $params ) {
    return new self( $params['align'], $params['boilerplate'], $params['label'], $params['title'], $params['width'] );
  }
  
  /**
   * Creates a BoilerRoomBox instance from the provided values. All values are run through the proper set function
   * to normalize and sanitize them for use before being assigned.
   * @param string $align The alignment value to set.
   * @param string $boilerplate The title text to use the set the boilerplate title.
   * @param string $label The button label this box should use.
   * @param string $title The default title to set.
   * @param int $width The width this box should be.
   */
  public function __construct( $align, $boilerplate, $label, $title, $width ) {
    $this->setAlign( $align );
    $this->setBoilerplate( $boilerplate );
    $this->setLabel( $label );
    $this->setTitle( $title );
    $this->setWidth( $width );
  }
  
  /**
   * Gets the alignment of this box.
   * @return string The alignment of this box.
   */
  public function getAlign() {
    return $this->mAlign;
  }
  
  /**
   * Sets, validates, and normalizes an alignment value for the text-align property.
   * @param string $align The alignment value to set.
   */
  public function setAlign( $align ) {
    if ( isset( $align ) ) {
      switch ( trim( strtolower( $align ) ) ) {
        case "center":
          $this->mAlign = "center";
        case "left":
          $this->mAlign = "left";
        case "right":
          $this->mAlign = "right";
        case "justify":
          $this->mAlign = "justify";
        case "inherit":
          $this->mAlign = "inherit";
        default:
          $this->mAlign = null;
      }
    } else {
      $this->mAlign = null;
    }
  }
  
  /**
   * Gets the title of the boilerplate this box uses.
   * @return string The title of the boilerplate this box uses.
   */
  public function getBoilerplate() {
    return $this->mBoilerplate;
  }
  
  /**
   * Sets the title of the boilerplate this box uses. Applies automatic namespace prefixing and uses Title to
   * validate the title text. Sets to null if no valid title is provided.
   * @param string $boilerplate The title text to use the set the boilerplate title.
   */
  public function setBoilerplate( $boilerplate ) {
    if ( isset( $boilerplate ) ) {
      $title = BoilerplateNamespace::getInstance()->boilerplateTitleFromText( $boilerplate, false ); 
      if ( $title !== null ) {
        $this->mBoilerplate = $title->getPrefixedText();
      } else {
        $this->mBoilerplate = null;
      }
    } else {
      $this->mBoilerplate = null;
    }
  }
  
  /**
   * Gets the button label this box uses.
   * @return string The button label this box uses.
   */
  public function getLabel() {
    return $this->mLabel;
  }
  
  /**
   * Sets the button label this box uses. Setting to an empty or unset values cause the default label to be assigned
   * instead.
   * @param string $label The button label this box should use.
   */
  public function setLabel( $label) {
    if ( isset( $label ) && $label !== '' ) {
      $this->mLabel = $label;
    } else {
      $this->mLabel = wfMessage( 'br-default-box-label' )->text();
    }
  }
  
  /**
   * Gets the default title this box uses.
   * @return string The default title this box uses.
   */
  public function getTitle() {
    return $this->mTitle;
  }
  
  /**
   * Sets the default title this box uses. Unset values result in the empty string being assigned instead.
   * Underscores are converted to spaces.
   * @param string $title The default title to set.
   */
  public function setTitle( $title ) {
    if ( isset( $title ) ) {
      $this->mTitle = str_replace( '_', ' ', $title );
    } else {
      $this->mTitle = "";
    }
  }
  
  /**
   * Gets the width of this box.
   * @return int The width of this box.
   */
  public function getWidth() {
    return $this->mWidth;
  }
  
  /**
   * Sets the width of this box. Unset or values less than one result in the default width being set instead.
   * @param int $width The width this box should be.
   */
  public function setWidth( $width ) {
    if ( isset( $width ) && intval( $width ) > 0 ) {
      $width = intval( $width );
    } else {
      $width = 30;
    }
  }
  
  /**
   * This function creates the boilerplate form markup and returns it.
   * @global $wgScript The path to index.php.
   * @return The boiler room box form markup.
   */
  public function render( ) {
    global $wgScript;
    
    $submit = htmlspecialchars( $wgScript );

    $style = '';
    if ( $this->mAlign !== null) {
      $style = 'text-align: ' . $this->mAlign; 
    }      

    $boilerplate = '';
    if ( $this->mBoilerplate !== null ) {
      $boilerplate = Xml::input( 'boilerplate', false, $this->mBoilerplate, Array( 'type' => 'hidden' ) );
    }

    $output = Xml::openElement( 'div', Array( 'class' => 'boilerRoomBox',
                                              'style' => $style,
                                            ) 
                              ) .
              Xml::openElement( 'form', Array( 'name'    => 'boilerRoomBox',
                                               'action'  => $submit,
                                               'method'  => 'get',
                                               'class'   => 'boilerRoomBoxForm',
                                             ) 
                              ) .
              Xml::input( 'action', false, 'edit', Array( 'type' => 'hidden' ) ) .
              $boilerplate .
              Xml::input( 'title', $this->mWidth, $this->mTitle, Array( 'type'   => 'text',
                                                                       'class'  => 'boilerRoomBoxInput',
                                                                     ) 
                        ) .
              Xml::submitButton( $this->mLabel, Array( 'class' => 'boilerRoomBoxButton' ) ) .
              Xml::closeElement( 'form' ) .
              Xml::closeElement( 'div' );

    return $output;
  }
}