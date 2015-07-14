var boilerRoom = boilerRoom || {
  state : null,
  messages : null,
  
  /**
   * Adds messages to the object. Called by generated JS in BoilerRoomSelector.php
   */
  addMessages : function( messages ) {
    boilerRoom.messages = messages;
  },
  
  /**
   * Adds state information to the object. Called by generated JS in BoilerRoomSelector.php
   */
  addState : function( state ) {
    boilerRoom.state = state;
  },
  
  /**
   * Main function for initializing the BoilerRoom selector.
   */
  initializeAjaxSelector : function() {
    if ( boilerRoom.state.showExistenceMessage ) {
      boilerRoom.displayPageExistsMsg();
    }
    
    mw.loader.using( 'user.options', function() {
      if ( mw.user.options.get( 'usebetatoolbar' ) ) {
        mw.loader.using( 'ext.wikiEditor.toolbar', function() {
          $( '#boilerRoomMessage' ).appendTo(".wikiEditor-ui-buttons");
          $( '#boilerRoomMessage' ).css( { 
            'float' : 'right',
            'width' : '20em',
            'margin-left' : '1em',
            'margin-top' : '-0.5em'
          } );
          $( '#wikiEditor-ui-toolbar' ).prepend(
            '<div id="boilerRoomSelectorContainer" style="float: right; line-height: 22px; margin: 5px;"></div>');
          boilerRoom.initializeSmallAjaxSelector();
        } );
      }
    } );
      
    if ( $( '#boilerRoomSelectorContainer' ).length ) {
      if ( boilerRoom.state.useLargeSelector ) {
        boilerRoom.initializeLargeAjaxSelector();
      } else {
        boilerRoom.initializeSmallAjaxSelector();
      }
    }
  },

  /**
   * Constructs the HTML elements for the small AJAX selector and inserts them into the selector container.
   */
  initializeSmallAjaxSelector : function() {
    $( '#boilerRoomSelectorContainer' ).html(
      '<div style="float: right;" ><select id="boilerRoomSelect" size="1">' + boilerRoom.state.standardHtmlList + 
      '</select>' +
      '<img src="' + mw.config.get( 'wgScriptPath' ) + '/extensions/BoilerRoom/images/button-insert.png" ' +
      'alt="' + boilerRoom.messages.selectorInsert + '" ' + 
      'title="' + boilerRoom.messages.selectorInsert + '" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateInsert );">' +
      '<img src="' + mw.config.get( 'wgScriptPath' ) + '/extensions/BoilerRoom/images/button-prepend.png" ' +
      'alt="' + boilerRoom.messages.selectorPrepend + '" ' + 
      'title="' + boilerRoom.messages.selectorPrepend + '" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplatePrepend );">' + 
      '<img src="' + mw.config.get( 'wgScriptPath' ) + '/extensions/BoilerRoom/images/button-append.png" ' +
      'alt="' + boilerRoom.messages.selectorAppend + '" ' + 
      'title="' + boilerRoom.messages.selectorAppend + '" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateAppend );">' + 
      '<img src="' + mw.config.get( 'wgScriptPath' ) + '/extensions/BoilerRoom/images/button-replace.png" ' +
      'alt="' + boilerRoom.messages.selectorReplace + '" ' + 
      'title="' + boilerRoom.messages.selectorReplace + '" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateReplace );">' +
      '</div>'
    );
  },

  /**
   * Constructs the HTML elements for the large AJAX selector and inserts them into the selector container.
   */
  initializeLargeAjaxSelector : function() {
    $( '#boilerRoomSelectorContainer' ).html(
      '<fieldset class="boilerRoomFieldSet" style="clear: both;">' +
      '<legend>' + boilerRoom.messages.selectorLegend + '</legend>' +
      '<table class="boilerRoomSelector" style="width: 100%;"><tr>' +
      '<td style="padding: 0 2em 0 0; width: 40%;">' +
      '<select id="boilerRoomSelect" size="6" style="width: 100%;">' + boilerRoom.state.standardHtmlList +
      '</select>' +
      '</td><td style="padding: 0 0 0 2em; width: 30%;">' +
      '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateInsert );">' + boilerRoom.messages.selectorInsert + 
      '</button>' +
      '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateReplace );">' + boilerRoom.messages.selectorReplace + 
      '</button>' +
      '</td><td style="padding: 0 0 0 2em; width: 30%;">' +
      '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplatePrepend );">' + boilerRoom.messages.selectorPrepend +
      '</button>' +
      '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
      'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateAppend );">' + boilerRoom.messages.selectorAppend + 
      '</button>' +
      '</td></tr></table>' +
      '</fieldset>'
    );
  },

  /**
   * Displays the appropriate page exists message for the AJAX selector
   */
  displayPageExistsMsg : function() {
    $( '#boilerRoomMessage' ).html( boilerRoom.messages.pageExistsAjaxMsg );
  },

  /**
   * Returns the title of the currently selected boilerplate, or
   * an empty string if none is selected.
   */
  getSelectedTitle : function() {
    return $("#boilerRoomSelect").children("option").filter(":selected").text()
  },

  /**
   * Calls into the BoilerRoom extension to fetch the boilerplate
   * text and then directs it to the indicated target function.
   */
  boilerplateFetch : function ( target ) {
    if ( boilerRoom.confirmAction( target ) ) {
      var title = boilerRoom.getSelectedTitle();
      if ( title != '' ) {
        $.ajax( {
          type: 'GET',
          url: mw.config.get( 'wgScriptPath' ) + "/api.php",
          data: { action: 'boilerplate', format: 'json', title: title },
          dataType: 'json',
          success: target
        } );
      }
    }
  },

  /**
   * Raises a confirmation dialog for replacement and returns the result.
   * Simply returns true for prepending and appending.
   */
  confirmAction : function( target ) {
    if ( target == boilerRoom.boilerplateInsert )
      return true;
    else if ( target == boilerRoom.boilerplatePrepend )
      return true;
    else if ( target == boilerRoom.boilerplateAppend )
      return true;
    else if ( target == boilerRoom.boilerplateReplace )
      return confirm("Are you sure you want to replace the existing text?\n\n" +
                     "You may not be able to undo this action."
                    );
    else
      return false;  // shouldn't get here.
  },

  /**
   * Prepends the indicated content to the content within the edit box.
   */
  boilerplateInsert : function( content ) {
    var editarea = $( '#wpTextbox1' );
    boilerRoom.insertAtCursor( editarea, boilerRoom.getBoilerplateContent( content ) );
    $( '#boilerRoomMessage' ).html(
      boilerRoom.messages.insertMsg.replace('$1', boilerRoom.getSelectedTitle() ) );
  },

  /**
   * Prepends the indicated content to the content within the edit box.
   */
  boilerplatePrepend : function( content ) {
    var editarea = $( '#wpTextbox1' );
    editarea.val( boilerRoom.getBoilerplateContent( content ) + editarea.val() );
    $( '#boilerRoomMessage' ).html(
      boilerRoom.messages.prependMsg.replace('$1', boilerRoom.getSelectedTitle() ) );
  },

  /**
   * Appends the indicated content to the content within the edit box.
   */
  boilerplateAppend : function( content ) {
    var editarea = $( '#wpTextbox1' );
    editarea.val( editarea.val() + boilerRoom.getBoilerplateContent( content ) );
    $( '#boilerRoomMessage' ).html(
      boilerRoom.messages.appendMsg.replace('$1', boilerRoom.getSelectedTitle() ) );
  },

  /**
   * Replaces the content within the edit box with the boilerplate content.
   */
  boilerplateReplace : function( content ) {
    var editarea = $( '#wpTextbox1' );
    editarea.val( boilerRoom.getBoilerplateContent( content ) );
    $( '#boilerRoomMessage' ).html(
      boilerRoom.messages.replaceMsg.replace('$1', boilerRoom.getSelectedTitle() ) );
  },

  /**
   * Performs the steps needed to insert text at the cursor position in a
   * textarea.
   */
  insertAtCursor : function( textarea, text ) {
    if (document.selection) {
      textarea.focus();
      selection = document.selection.createRange();
      selection.text = text;
    } else if (textarea[0].selectionStart || textarea[0].selectionStart == '0') {
      var start = textarea[0].selectionStart;
      var end = textarea[0].selectionEnd;
      textarea.val( textarea.val().substring( 0, start ) + text + 
        textarea.val().substring( end, textarea.val().length ) );
    } else {
      textarea.val( textarea.val() + text );
    }
  },

  getBoilerplateContent : function( jsonData ) {  
    if ( jsonData.boilerplate && jsonData.boilerplate['*'] ) {
      return jsonData.boilerplate['*'];
    } else {
      return 'none';
    }
  }
}