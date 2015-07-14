/**
 * Main function for initializing the BoilerRoom selector.
 */
function initializeAjaxSelector( useLargeSelector,
                                    showExistenceMessage, 
                                    options, 
                                    legend,
                                    insertLabel, 
                                    replaceLabel, 
                                    prependLabel, 
                                    appendLabel
                                  ) {
  if ( showExistenceMessage ) 
    displayPageExistsMsg();
  
  if ( useLargeSelector ) 
    initializeLargeAjaxSelector( options, legend, insertLabel, replaceLabel, prependLabel, appendLabel );
  else
    initializeSmallAjaxSelector( options, insertLabel, replaceLabel, prependLabel, appendLabel );
}

function initializeSmallAjaxSelector( options, insertLabel, replaceLabel, prependLabel, appendLabel ) {
  var boilerRoomSelectorContainer = document.getElementById("boilerRoomSelectorContainer");
  boilerRoomSelectorContainer.innerHTML =
    '<div style="float: right;" ><select id="boilerRoomSelect" size="1">' + options + '</select>' +
    '<img src="' + wgbrScriptPath + '/extensions/BoilerRoom/images/button-insert.png" ' +
    'alt="' + insertLabel + '" ' + 
    'title="' + insertLabel + '" ' +
    'onclick="boilerplateFetch( boilerplateInsert );">' +
    '<img src="' + wgbrScriptPath + '/extensions/BoilerRoom/images/button-prepend.png" ' +
    'alt="' + prependLabel + '" ' + 
    'title="' + prependLabel + '" ' +
    'onclick="boilerplateFetch( boilerplatePrepend );">' + 
    '<img src="' + wgbrScriptPath + '/extensions/BoilerRoom/images/button-append.png" ' +
    'alt="' + appendLabel + '" ' + 
    'title="' + appendLabel + '" ' +
    'onclick="boilerplateFetch( boilerplateAppend );">' + 
    '<img src="' + wgbrScriptPath + '/extensions/BoilerRoom/images/button-replace.png" ' +
    'alt="' + replaceLabel + '" ' + 
    'title="' + replaceLabel + '" ' +
    'onclick="boilerplateFetch( boilerplateReplace );">' +
    '</div>';
    
}

function initializeLargeAjaxSelector( options, legend, insertLabel, replaceLabel, prependLabel, appendLabel ) {
  var boilerRoomSelectorContainer = document.getElementById("boilerRoomSelectorContainer");
  boilerRoomSelectorContainer.innerHTML =
    '<fieldset class="boilerRoomFieldSet" style="clear: both;">' +
    '<legend>' + legend + '</legend>' +
    '<table class="boilerRoomSelector" style="width: 100%;"><tr>' +
    '<td style="padding: 0 2em 0 0; width: 40%;">' +
    '<select id="boilerRoomSelect" size="6" style="width: 100%;">' + options +
    '</select>' +
    '</td><td style="padding: 0 0 0 2em; width: 30%;">' +
    '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
    'onclick="boilerplateFetch( boilerplateInsert );">' + insertLabel + '</button>' +
    '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
    'onclick="boilerplateFetch( boilerplateReplace );">' + replaceLabel + '</button>' +
    '</td><td style="padding: 0 0 0 2em; width: 30%;">' +
    '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
    'onclick="boilerplateFetch( boilerplatePrepend );">' + prependLabel + '</button>' +
    '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
    'onclick="boilerplateFetch( boilerplateAppend );">' + appendLabel + '</button>' +
    '</td></tr></table>' +
    '</fieldset>';
}

/**
 * Displays the appropriate page exists message for the AJAX
 * selector
 */
function displayPageExistsMsg() {
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrPageExistsAjaxMsg;
}

/**
 * Returns the title of the currently selected boilerplate, or
 * an empty string if none is selected.
 */
function getSelectedTitle() {
  var boilerRoomSelect = document.getElementById('boilerRoomSelect');
  if ( boilerRoomSelect.selectedIndex > -1 ) {
    var selectedOption = boilerRoomSelect.options[boilerRoomSelect.selectedIndex];
    return selectedOption.text;
  } else {
    return '';
  }
}

/**
 * Calls into the BoilerRoom extension to fetch the boilerplate
 * text and then directs it to the indicated target function.
 */
function boilerplateFetch( target ) {
  if ( confirmAction( target ) ) {
    var title = getSelectedTitle()
    if ( title != '' ) {
      $.get( wgbrScriptPath + "/api.php?action=boilerplate&title=" + escape( title ) + "&format=xml",
             target
           );
    }
  }
}

/**
 * Raises a confirmation dialog for replacement and returns the result.
 * Simply returns true for prepending and appending.
 */
function confirmAction( target ) {
  if ( target == boilerplateInsert )
    return true;
  else if ( target == boilerplatePrepend )
    return true;
  else if ( target == boilerplateAppend )
    return true;
  else if ( target == boilerplateReplace )
    return confirm("Are you sure you want to replace the existing text?\n\n" +
                   "You may not be able to undo this action."
                  );
  else
    return false;  // shouldn't get here.
}

/**
 * Prepends the indicated content to the content within the edit box.
 */
function boilerplateInsert( content ) {
  var editarea = document.getElementById("wpTextbox1");
  insertAtCursor( editarea, getBoilerplateContent( content ) );
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrInsertMsg.replace('$1', getSelectedTitle() );
}

/**
 * Prepends the indicated content to the content within the edit box.
 */
function boilerplatePrepend( content ) {
  var editarea = document.getElementById("wpTextbox1");
  editarea.value = getBoilerplateContent( content ) + editarea.value;
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrPrependMsg.replace('$1', getSelectedTitle() );
}

/**
 * Appends the indicated content to the content within the edit box.
 */
function boilerplateAppend( content ) {
  var editarea = document.getElementById("wpTextbox1");
  editarea.value = editarea.value + getBoilerplateContent( content );
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrAppendMsg.replace('$1', getSelectedTitle() );
}

/**
 * Replaces the content within the edit box with the boilerplate content.
 */
function boilerplateReplace( content ) {
  var editarea = document.getElementById("wpTextbox1");
  editarea.value = getBoilerplateContent( content );
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrReplaceMsg.replace('$1', getSelectedTitle() );
}

/**
 * Performs the steps needed to insert text at the cursor position in a
 * textarea.
 */
function insertAtCursor( textarea, text ) {
  if (document.selection) {
    textarea.focus();
    selection = document.selection.createRange();
    selection.text = text;
  } else if (textarea.selectionStart || textarea.selectionStart == '0') {
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    textarea.value = textarea.value.substring(0, start) + text + 
      textarea.value.substring(end, textarea.value.length);
  } else {
    textarea.value += text;
  }
}

function getBoilerplateContent( content ) {  
  var elements = content.getElementsByTagName('boilerplate');
  
  if ( elements.length > 0 ) {
    return elements[0].textContent;
  } else {
    return 'none';
  }
}

initializeBoilerRoom();