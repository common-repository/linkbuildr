jQuery(
	function($){
		$( document ).ready(
			function($) {
				// Check for HTML5 File capabilities.
				if (window.File && window.FileReader && window.FileList && window.Blob) {
					// Add Listener for pre-form change - this is where File is selected.
					let lbImporterPreForm = document.getElementById( 'lb-importer-pre-form' );
					if ( lbImporterPreForm ) {
						lbImporterPreForm.addEventListener( 'change', handleFileSelect, false );
					}

					// Add listener fpr data-form change - this is where data is shown.
					let lbImporterForm = document.getElementById( 'lb-importer-data-form' );
					if ( lbImporterForm ) {
						lbImporterForm.addEventListener( 'change', debounce( reValidateCellEvent, 250 ), false );
					}

				} else {
					// If HTML5 File capabilities not found, display an error.
					let data_error_markup                                   = '<div class="summary error"><div class="lb-stat-body">The File APIs are not fully supported in this browser. Please use a&nbsp;<a href="https://developer.mozilla.org/en-US/docs/Web/API/File#Browser_compatibility" target="_blank" rel="nofollow">broswer that supports File APIs.</a></div></div>';
					document.getElementById( 'file-data-errors' ).innerHTML = data_error_markup;
				}

				// Catch pre-form submit and don't do anything with it.
				$( '#lb-importer-pre-form' ).submit(
					function (event) {
						event.preventDefault();
						return false;
					}
				);

				// Catch data-form submit and process only if it has valid data.
				$( '#lb-importer-data-form' ).submit(
					function (event) {
						event.preventDefault();
						if ($( '#lb-submit-import' ).val() == 'false') {
							return false;
						} else {
							condenseForms();
							$( '#lb-importer-form' ).submit();
						}
					}
				);
			}
		);
	}
);

/***** Global Variable Definitions ******/

var tableheaderTHIdPrefix     = 'lb-table-th-';
var tableheaderIdPrefix       = 'lb-table-header-';
var tableheaderClass          = 'lb-table-header';
var tableheaderDraggableClass = 'lb-table-header-draggable';

var tableheaderContainerIdPrefix   = 'lb-table-header-container-';
var tableheaderContainerClass      = 'lb-table-header-container';
var tableheaderEmptyContainerClass = 'lb-empty-header-container';

var linkbuildrInputRowClassPrefix = 'lb_inputs_in_row_';

var linkbuildrDataDelimiter = '#zlbz#';

var linkbuildrImportDataName = 'lb_import_data';
var tableDataValidityKeyName = 'lb_import_data_validity';

var tableCellBaseClass    = 'import-table-cell';
var tableCellClassPrefix  = 'import-table-cell-';
var tableCellInvalidClass = 'lb-invalid-cell';

var tableRowSuccessClass = 'lb-row-success';
var tableRowErrorClass   = 'lb-row-error';

var data_summary = {};

var requiredColumns = [ 'site', 'email', 'domain', 'name' ];

/***** File Events ******/
/**
 * Adds basic file data on screen and adds the listener for the file being loaded.
 *
 * @param {object} evt - the Event Object corresponding to the File being Selected.
 */
function handleFileSelect(evt) {
	clearDynamicContent();
	var files             = evt.target.files;
	var fileDetailsOutput = [];
	if (files !== undefined && files !== null) {
		var reader    = new FileReader();
		reader.onload = (function(theFile) {
			return function(e) {
				fileDetailsOutput.push(
					'<li><strong>',
					escape( theFile.name ),
					'</strong> (',
					theFile.type || 'n/a',
					') - ',
					theFile.size,
					' bytes, last modified: ',
					theFile.lastModifiedDate ? theFile.lastModifiedDate.toLocaleDateString() : 'n/a',
					'</li>'
				);
				document.getElementById( 'file-details' ).innerHTML = '<ul>' + fileDetailsOutput.join( '' ) + '</ul>';
			};
		})( files[0] );

		if ( 'text/csv' === files[0].type ) {
			reader.readAsText( files[0] );
			reader.addEventListener( 'loadend', handleFileLoaded, false );
		} else {
			let data_error_markup                                   = '<div class="summary error"><div class="lb-stat-body">Incorrect File Type, please only upload CSV formatted files.</div></div>';
			document.getElementById( 'file-data-errors' ).innerHTML = data_error_markup;
		}
	}
}

/**
 * Loads in the file, fires off it's first validation and displays the data on page if the file passses first validation.
 *
 * @param {object} evt - the Event Object corresponding to the File being Loaded.
 */
function handleFileLoaded(evt) {
	var data = evt.currentTarget.result;
	var rows = data.split( "\n" );

	var validFile = scanFileForRequirements( rows );

	var stats = {
		header_first: false,
		row_count: 0,
		col_count: 0,
		col_map: {}
	};

	var results_data = [];

	var startingRow = 0;

	if ( validFile ) {
		stats.header_first = true;
		startingRow        = 1;
		var headers        = rows[0].split( ',' );
		var datamap        = mapDataTypesFromHeaders( headers );
		setColumnMap( datamap );
		stats.col_map   = datamap;
		stats.col_count = Object.keys( datamap ).length;

		for (var i = startingRow, r; r = rows[i]; i++) {
			results_data.push( r.split( ',' ) );
		}

		data_summary = createDataTable( results_data, stats );
		displaySummaryAndErrors();
	}
}

/***** File Validation ******/
/**
 * Quickly checks the formatting of the file to see if it meets minimum requirements for processing.
 *
 * @param {array} fileRows - the data from the file split into rows, but not columns yet
 */
function scanFileForRequirements(fileRows) {
	// Check for Headers.
	// Check for Header values: domain, site, name, email.
	// Check for Column count.
	// Check for Row count.

	let rowCount    = fileRows.length;
	let maxRowCount = document.getElementById( 'php_max_input_vars' ).value;

	var fileValidationChecks = {
		isValid: false,
		hasHeaders: false,
		headersHaveDomain: false,
		headersHaveSite: false,
		headersHaveName: false,
		headersHaveEmail: false,
		columnCountValid: false,
		rowCountValid: (rowCount < ( maxRowCount - 5 ) ? true : false)
	}

	let firstRow = fileRows[0].split( ',' );

	let maxColumnCount = firstRow.length;

	let headerRowValid = true;

	for (var i = 0, col; col = firstRow[i]; i++) {
		if ( col.toLowerCase().includes( 'domain' ) || col.toLowerCase().includes( 'url' ) ) {
			fileValidationChecks.headersHaveDomain = true;
		} else if ( col.toLowerCase().includes( 'site' ) ) {
			fileValidationChecks.headersHaveSite = true;
		} else if ( col.toLowerCase().includes( 'email' ) ) {
			fileValidationChecks.headersHaveEmail = true;
		} else if ( col.toLowerCase().includes( 'name' ) ) {
			fileValidationChecks.headersHaveName = true;
		}

		if (validateURL( col ) || validateEmail( col )) {
			headerRowValid = false;
		}
	}

	fileValidationChecks.hasHeaders = headerRowValid;

	for (var i = 1, row; row = fileRows[i]; i++) {
		if (i > maxRowCount) {
			break;
		}

		let currentRow = row.split( ',' );

		if (currentRow.length > maxColumnCount) {
			maxColumnCount = currentRow.length;
		}
	}

	if (4 >= maxColumnCount) {
		fileValidationChecks.columnCountValid = true;
	}

	if (fileValidationChecks.hasHeaders &&
		fileValidationChecks.headersHaveDomain &&
		fileValidationChecks.headersHaveSite &&
		fileValidationChecks.headersHaveName &&
		fileValidationChecks.headersHaveEmail &&
		fileValidationChecks.columnCountValid &&
		fileValidationChecks.rowCountValid) {
		fileValidationChecks.isValid = true;
	}

	updateFileRequirements( fileValidationChecks );

	return fileValidationChecks.isValid;
}

/**
 * Updates the File Requirements UI based on the results of scanFileForRequirements()
 *
 * @param {object} fileValidationChecks - contains the results of the validation checks
 */
function updateFileRequirements(fileValidationChecks){

	if ( fileValidationChecks.isValid) {
		document.getElementById( 'lb-file-requirements' ).classList.add( 'lb-file-valid' );
		document.getElementById( 'lb-file-requirements' ).classList.remove( 'lb-file-invalid' );
	} else {
		document.getElementById( 'lb-file-requirements' ).classList.add( 'lb-file-invalid' );
		document.getElementById( 'lb-file-requirements' ).classList.remove( 'lb-file-valid' );
		let data_error_markup                                   = '<div class="summary error"><div class="lb-stat-body"><span class="lb-big-num">Please resolve file requirement issues and try again.</span></div></div>';
		document.getElementById( 'file-data-errors' ).innerHTML = data_error_markup;

	}

	if ( fileValidationChecks.hasHeaders) {
		document.getElementById( 'lb-file-req-firstLine' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-firstLine' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-firstLine' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-firstLine' ).classList.remove( 'lb-req-passed' );
	}

	if ( fileValidationChecks.headersHaveDomain ) {
		document.getElementById( 'lb-file-req-includedHeaders-domain' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-includedHeaders-domain' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-includedHeaders-domain' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-includedHeaders-domain' ).classList.remove( 'lb-req-passed' );
	}

	if ( fileValidationChecks.headersHaveSite ) {
		document.getElementById( 'lb-file-req-includedHeaders-site' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-includedHeaders-site' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-includedHeaders-site' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-includedHeaders-site' ).classList.remove( 'lb-req-passed' );
	}

	if ( fileValidationChecks.headersHaveName ) {
		document.getElementById( 'lb-file-req-includedHeaders-name' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-includedHeaders-name' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-includedHeaders-name' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-includedHeaders-name' ).classList.remove( 'lb-req-passed' );
	}

	if ( fileValidationChecks.headersHaveEmail ) {
		document.getElementById( 'lb-file-req-includedHeaders-email' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-includedHeaders-email' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-includedHeaders-email' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-includedHeaders-email' ).classList.remove( 'lb-req-passed' );
	}

	if ( fileValidationChecks.headersHaveDomain && fileValidationChecks.headersHaveSite && fileValidationChecks.headersHaveName && fileValidationChecks.headersHaveEmail ) {
		document.getElementById( 'lb-file-req-includedHeaders' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-includedHeaders' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-includedHeaders' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-includedHeaders' ).classList.remove( 'lb-req-passed' );
	}

	if ( fileValidationChecks.columnCountValid ) {
		document.getElementById( 'lb-file-req-columnLimit' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-columnLimit' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-columnLimit' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-columnLimit' ).classList.remove( 'lb-req-passed' );
	}

	if ( fileValidationChecks.rowCountValid ) {
		document.getElementById( 'lb-file-req-rowLimit' ).classList.add( 'lb-req-passed' );
		document.getElementById( 'lb-file-req-rowLimit' ).classList.remove( 'lb-req-failed' );
	} else {
		document.getElementById( 'lb-file-req-rowLimit' ).classList.add( 'lb-req-failed' );
		document.getElementById( 'lb-file-req-rowLimit' ).classList.remove( 'lb-req-passed' );
	}
}

/****** Markup Generation ******/
/**
 * Takes the information already acquired from the file and the rest of file data, and generates a table of <input> elements allowing for data editing in browser
 *
 * @param {object} data - the data rows of the uploaded csv file
 * @param {object} stats - the stats already acquired from the file
 */
function createDataTable(data, stats){
	var defaultEmailTemplateSelect = document.getElementById( "default_email_template_id" );

	var rowCount      = 0;
	var errorCount    = 0;
	var errorRowCount = 0;
	var validRowCount = 0;

	var tableMarkup = '<table class="import-contact-table">';
	tableMarkup    += '<thead>';
	tableMarkup    += '<tr>';
	for (var y = 0, head; head = stats.col_map[y]; y++) {
		tableMarkup += '<th id="'
			+ tableheaderTHIdPrefix + head
			+ '"><div id="'
			+ tableheaderContainerIdPrefix
			+ y
			+ '" class="'
			+ tableheaderContainerClass
			+ '" columnNumber="'
			+ y
			+ '" ondrop="dropHeader(event)" ondragover="allowDrop(event)"><div id="'
			+ tableheaderIdPrefix
			+ head
			+ '" class="'
			+ tableheaderClass
			+ ' '
			+ tableheaderDraggableClass
			+ ' '
			+ tableCellClassPrefix + head
			+ '" columnNumber="'
			+ y
			+ '" dataTypeAssociation="'
			+ head
			+ '" draggable="true" ondragstart="dragHeader(event)">'
			+ capitalizeFirstLetter( head )
			+ '</div></div></th>';
	}
	tableMarkup += '<th id="' + tableheaderTHIdPrefix + 'template"><div class="' + tableheaderClass + '">Email Template</div></th>';
	tableMarkup += '</tr>';
	tableMarkup += '</thead>';
	tableMarkup += '<tbody>';
	for (var y = 0; y < data.length; y++) {
		let row = data[y];
		let rowValid  = true;
		let rowMarkup = '';
		for (var x = 0; x < row.length; x++) {
			let cell = row[x];
			let cellValid       = true;
			let cellMarkup      = '';
			let cellColumnClass = '';

			let cellClasses = tableCellBaseClass;

			if ('email' === stats.col_map[x]) {
				cellValid = validateEmail( cell );
				if ( ! cellValid) {
					rowValid = false;
					errorCount++;
				}
				cellColumnClass = tableCellClassPrefix + 'email';
				cellClasses    += ' ' + cellColumnClass;
			}
			if ('domain' === stats.col_map[x]) {
				cellValid = validateURL( cell );
				if ( ! cellValid) {
					rowValid = false;
					errorCount++;
				}
				cellColumnClass = tableCellClassPrefix + 'domain';
				cellClasses    += ' ' + cellColumnClass;
			}

			if ('site' === stats.col_map[x]) {
				cellColumnClass = tableCellClassPrefix + 'site';
				cellClasses    += ' ' + cellColumnClass;
			}

			if ('name' === stats.col_map[x]) {
				cellColumnClass = tableCellClassPrefix + 'name';
				cellClasses    += ' ' + cellColumnClass;
			}

			cellMarkup = createInputMarkup( 'text', linkbuildrImportDataName + '[' + y + '][' + x + ']', '', linkbuildrInputRowClassPrefix + y, cell );

			cellClasses += (cellValid ? '' : ' ' + tableCellInvalidClass);
			rowMarkup   += '<td class="' + cellClasses + '">';
			rowMarkup   += cellMarkup;
			rowMarkup   += '</td>';
		}

		rowMarkup += '<td class="'
			+ tableCellBaseClass
			+ ' '
			+ tableCellClassPrefix
			+ 'template">'
			+ createRowSelect( y, defaultEmailTemplateSelect )
			+ createInputMarkup( 'hidden', tableDataValidityKeyName + '[' + y + ']', '', '', rowValid )
			+ '</td>';

		if (rowValid) {
			tableMarkup += '<tr class="' + tableRowSuccessClass + '">' + rowMarkup + '</tr>';
			validRowCount++;
		} else {
			tableMarkup += '<tr class="' + tableRowErrorClass + '">' + rowMarkup + '</tr>';
			errorRowCount++;
		}
		rowCount++;

	}

	tableMarkup += "</tbody>";
	tableMarkup += "</table>";

	var tableOutputDiv       = document.getElementById( "file-data-table-output" );
	tableOutputDiv.innerHTML = tableMarkup;

	return {
		row_count: rowCount,
		valid_row_count: validRowCount,
		error_row_count: errorRowCount,
		error_count: errorCount
	}
}

/**
 * Takes values in and returns a string of markup that is an input element.
 *
 * @param {string} type - type of the input (only 'text' and 'hidden' are tested/used)
 * @param {string} name - name of the input
 * @param {string} id - Optional - id of the input
 * @param {string} className - Optional - class or classes for the input
 * @param {string} value - Optional - value of the input
 */
function createInputMarkup(type, name, id = '', className = '', value = ''){
	let retval = '<input type="' + type + '" name="' + name + '" ';

	if ( '' !== id ) {
		retval += 'id="' + id + '" ';
	}

	if ( '' !== className ) {
		retval += 'class="' + className + '" ';
	}

	if ( '' !== value ) {
		retval += 'value="' + value + '" ';
	}

	retval += '/>';
	return retval;
}

/**
 * Generates a Select Input for use in Selecting the Email Template for a given row in the Table of data
 *
 * @param {int} row - the row in the table this is going in
 * @param {int} defaultSelect - the value to be selected when the select is created
 */
function createRowSelect(row, defaultSelect) {
	let rowSelect = document.createElement( "SELECT" );
	rowSelect.setAttribute( 'name', 'email_template_ids[' + row + ']' );
	rowSelect.setAttribute( 'class', linkbuildrInputRowClassPrefix + row );
	rowSelect.innerHTML = defaultSelect.innerHTML;
	rowSelect.options[defaultSelect.selectedIndex].setAttribute( "selected", "selected" );
	return rowSelect.outerHTML;
}

/**
 * Displays the Summary and Error data in the corresponding locations
 */
function displaySummaryAndErrors() {
	if ( 0 < data_summary.error_count ) {
		let data_error_markup                                   = '<div class="summary error"><div class="lb-stat-body"><span class="lb-big-num">' + data_summary.error_count + ' Error' + (data_summary.error_count > 1 ? 's' : '') + ' </span> across <span class="lb-big-num">' + data_summary.error_row_count + ' Row' + (data_summary.error_row_count > 1 ? 's' : '') + '</span> click in the erroring field' + (data_summary.error_count > 1 ? 's' : '') + ' to edit</div></div>';
		document.getElementById( 'file-data-errors' ).innerHTML = data_error_markup;

		let data_summary_markup                                  = '<div class="summary info"><div class="lb-stat-body"><span class="lb-big-num">' + (data_summary.row_count - data_summary.error_row_count) + ' Valid Rows</span> out of <span class="lb-big-num">' + data_summary.row_count + ' Total Rows</span></div></div>';
		document.getElementById( 'file-data-summary' ).innerHTML = data_summary_markup;
	} else {
		let data_summary_markup                                  = '<div class="summary info"><div class="lb-stat-body"><span class="lb-big-num">' + data_summary.row_count + ' Rows</span> of Contacts</div></div>';
		document.getElementById( 'file-data-summary' ).innerHTML = data_summary_markup;
		document.getElementById( 'file-data-errors' ).innerHTML  = '';
	}

	let submitRow = document.getElementById( 'lb-submit-row' );
	if ( data_summary.row_count - data_summary.error_row_count > 0 ) {
		submitRow.classList.remove( 'lb-importer-submit-row' );
		document.getElementById( 'lb-submit-import' ).value = true;
	} else {
		if ( ! submitRow.classList.contains( 'lb-importer-submit-row' )) {
			submitRow.classList.add( 'lb-importer-submit-row' );
			document.getElementById( 'lb-submit-import' ).value = false;
		}
	}

}

/****** Data Mapping ******/
/**
 * Gets the Column to Data Type mapping from the hidden form inputs which are updated on change, and returns an object of the map.
 * return:
 * key - column number
 * value - data type name
 */
function mapDataTypesFromStore() {
	var datamap       = {};
	var lbStore       = document.getElementsByClassName( 'lb-datamap-store' );
	var lbStoreLength = lbStore.length;

	for (var i = 0; i < lbStoreLength; i++) {
		let dataType     = parseInputNameKey( lbStore[i].getAttribute( 'name' ) );
		let columnNumber = lbStore[i].value;
		if (columnNumber != '-') {
			datamap[columnNumber] = dataType;
		}
	}
	return datamap;
}

/**
 * Goes over values from the file header and checks for values indicating datatype
 * Returns on object that is a map of key - column number, value - data type.
 *
 * @param {array} headers - header values from file
 */
function mapDataTypesFromHeaders(headers) {
	// Domain, Site Name, Name, Email, Email Template.
	var datamap = {};

	for (var i = 0, col; col = headers[i]; i++) {
		if ( col.toLowerCase().includes( 'domain' ) || col.toLowerCase().includes( 'url' ) ) {
			datamap[i] = 'domain';
		} else if ( col.toLowerCase().includes( 'site' ) ) {
			datamap[i] = 'site';
		} else if ( col.toLowerCase().includes( 'email' ) ) {
			datamap[i] = 'email';
		} else if ( col.toLowerCase().includes( 'name' ) || col.toLowerCase().includes( 'contact' ) ) {
			datamap[i] = 'name';
		}
	}

	return datamap;
}

/**
 * Sets the hidden inputs that track the data types corresponding to each column.
 *
 * @param {object} datamap - map of column number to data type
 */
function setColumnMap(datamap){
	for (var key in datamap) {
		document.getElementsByName( 'columnMap[' + datamap[key] + ']' )[0].value = key;
	}
}

/**
 * Updates a single hidden form element in the Column Map.
 *
 * @param {string} dataType - the Data Type to update
 * @param {string} newValue - new Data Type value
 */
function updateDataMap(dataType, newValue) {
	document.getElementsByName( 'columnMap[' + dataType + ']' )[0].value = newValue;
}

/****** Drag n' Drop Headers ******/
/**
 * Prevents Drag Over event functionality to allow for Drop functionality.
 *
 * @param {object} ev - event object passed on drag over
 */
function allowDrop(ev) {
	ev.preventDefault();
}

/**
 * Handles associating data with an object when it is initially dragged.
 *
 * @param {object} event - event object passed on drag
 */
function dragHeader(event) {
	event.dataTransfer.setData( "id", event.target.id );
	event.dataTransfer.setData( "sourceColumn", event.target.getAttribute( "columnNumber" ) );
}

/**
 * Handles updating everything on a header being dropped in a different headers location.
 *
 * @param {object} event - event object passed on drop
 */
function dropHeader(event) {
	event.preventDefault();
	var target = event.target;
	if ( ! target.className.includes( tableheaderContainerClass ) ) {
		target = target.parentElement;
	}

	var unassociatedHeaderHolder = document.getElementById( 'header-holder' );
	var draggedElementId         = event.dataTransfer.getData( 'id' );
	var draggedElement           = document.getElementById( draggedElementId );
	var sourceColumn             = event.dataTransfer.getData( 'sourceColumn' );
	var targetColumn             = target.getAttribute( 'columnNumber' );
	var dataTypeAssociation      = draggedElement.getAttribute( 'dataTypeAssociation' );

	var currentHeader                    = target.firstElementChild;
	var currentHeaderDataTypeAssociation = '';

	if ( currentHeader ) {
		currentHeaderDataTypeAssociation = currentHeader.getAttribute( 'dataTypeAssociation' );
		currentHeader.setAttribute( 'columnNumber', '-1' );

		unassociatedHeaderHolder.appendChild( currentHeader );
		if (unassociatedHeaderHolder.parentElement.classList.contains( 'hide' )) {
			unassociatedHeaderHolder.parentElement.classList.remove( 'hide' );
		}

		updateDataMap( currentHeaderDataTypeAssociation, '-' );
	}

	if ( sourceColumn != '-1' ) {
		let sourceColumnContainer = document.getElementById( tableheaderContainerIdPrefix + sourceColumn );
		sourceColumnContainer.classList.add( tableheaderEmptyContainerClass );
	}

	draggedElement.setAttribute( 'columnNumber', targetColumn );
	updateDataMap( dataTypeAssociation, targetColumn );
	target.innerHTML = '';
	target.classList.remove( tableheaderEmptyContainerClass );
	target.appendChild( draggedElement );

	reValidateColumn( sourceColumn );
	reValidateColumn( targetColumn );

	if ( unassociatedHeaderHolder.children.length <= 0 ) {
		unassociatedHeaderHolder.parentElement.classList.add( 'hide' );
	}
}

/****** Input Validation ******/
/**
 * Function to interface between event and functionality, so reValidateCell can be called independently of the event of form change.
 *
 * @param {object} event - the event object pass on Form change event fire
 */
function reValidateCellEvent(event){
	reValidateCell( event.target );
}

/**
 * Function to check the validity of a Cell.
 *
 * @param {object} cellToCheck - the event object pass on Form change event fire
 */
function reValidateCell(cellToCheck){
	let dataMap         = mapDataTypesFromStore();
	let changedInput    = cellToCheck;
	let parentCell      = changedInput.parentElement;
	let parentRow       = changedInput.parentElement.parentElement;
	let cellCoordinates = parseInputNameKey( changedInput.getAttribute( 'name' ) );
	let columnNumber    = cellCoordinates.col;
	let rowNumber       = cellCoordinates.row;

	let wasValid = ! parentCell.classList.contains( tableCellInvalidClass );
	let isValid  = wasValid;

	let rowWasValid = ! parentRow.classList.contains( tableRowErrorClass );
	let rowIsValid  = rowWasValid;

	if ('domain' === dataMap[columnNumber]) {
		isValid = validateURL( changedInput.value );
	} else if ('email' === dataMap[columnNumber]) {
		isValid = validateEmail( changedInput.value );
	} else {
		isValid = true;
	}

	if ( isValid ) {
		if ( ! wasValid ) {
			data_summary.error_count--;
			changedInput.parentElement.classList.remove( tableCellInvalidClass );
		}

		rowIsValid    = true;
		let cellCount = parentRow.children.length;
		for (var i = 0; i < cellCount; i++) {
			if ( parentRow.children[i].classList.contains( tableCellInvalidClass ) ) {
				rowIsValid = false;
			}
		}

		if ( rowIsValid ) {
			if ( ! rowWasValid ) {
				data_summary.error_row_count--;
				parentRow.classList.remove( tableRowErrorClass );
				parentRow.classList.add( tableRowSuccessClass );
				document.getElementsByName( tableDataValidityKeyName + '[' + rowNumber + ']' )[0].value = true;
			}
		}
	} else {
		if ( wasValid ) {
			data_summary.error_count++;
			changedInput.parentElement.classList.add( tableCellInvalidClass );
		}

		if ( rowWasValid ) {
			data_summary.error_row_count++;
			parentRow.classList.add( tableRowErrorClass );
			parentRow.classList.remove( tableRowSuccessClass );
			document.getElementsByName( tableDataValidityKeyName + '[' + rowNumber + ']' )[0].value = false;
		}
	}

	displaySummaryAndErrors();
}

function reValidateColumn(columnNumber) {
	if ( columnNumber > -1 ) {
		for (var i = 0; i < data_summary.row_count; i++) {
			let rowInputs = document.getElementsByClassName( linkbuildrInputRowClassPrefix + i );
			reValidateCell( rowInputs[columnNumber] );
		}
	}
}

/**
 * Uses Regex to validate whether the passed in string is an email or not.
 *
 * @param {string} email - string possibly containing an email
 */
function validateEmail(email) {
	var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return ! ! pattern.test( email.trim() );
}

/**
 * Uses HTML5 URL object to validate that the passed in string is a URL
 *
 * @param {string} url - a string possibly containing a URL
 */
function validateURL(url) {
	try {
		var testUrl = new URL( url );
		if ('https:' === testUrl.protocol || 'http:' === testUrl.protocol) {
			return true;
		} else {
			return false;
		}
	} catch (_) {
		return false;
	}
}

/**
 * Uses Regex to validate if the passed in string is a URL
 *
 * @param {string} url - a string possibly containing a URL
 */
function validateURLRegex(url) {
	var pattern = new RegExp(
		'^(https?:\\/\\/)?' + // protocol.
		'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name.
		'((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address.
		'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path.
		'(\\?[;&a-z\\d%_.~+=-]*)?' + // query string.
		'(\\#[-a-z\\d_]*)?$',
		'i'
	); // fragment locator.
	return ! ! pattern.test( url );
}

/****** Utilities ******/
/**
 * Takes the table of form elements and condenses it down into aprox. 1 form element per row.
 * This is done in order to allow for more rows to be imported, the limit being the php.ini max_input_vars setting value (default: 1000)
 */
function condenseForms() {
	let inputRowCount = 0;
	let skipCount     = 0;
	let inputsMarkup  = '';
	for (var i = 0; i < data_summary.row_count; i++) {
		let rowValidElement = document.getElementsByName( tableDataValidityKeyName + '[' + i + ']' )[0];
		if ( rowValidElement.value == 'true' ) {
			let rowInputs      = document.getElementsByClassName( linkbuildrInputRowClassPrefix + i );
			let rowInputValues = [];
			for (var j = 0, input; input = rowInputs[j]; j++ ) {
				if ( ! input.parentElement.parentElement.classList.contains( 'lb-row-error' )) {
					rowInputValues.push( input.value );
				}
			}
			inputsMarkup += createInputMarkup( 'hidden', 'lb-import-data[' + inputRowCount + ']', '', '', delimitValues( rowInputValues ) );
			inputRowCount++;
		} else {
			skipCount++;
		}
	}

	document.getElementById( 'lb-importer-skip-count' ).value                 = skipCount;
	document.getElementById( 'lb-importer-form-rowData-container' ).innerHTML = inputsMarkup;

	let dataMap = mapDataTypesFromStore();
	document.getElementById( 'lb-importer-form-columnMap-container' ).innerHTML = createInputMarkup( 'hidden', 'lb-colMap', '', '', delimitValues( dataMap ) );
}

/**
 * Takes an array and returns a delimited string of the arrays contents.
 *
 * @param {array} arr - an Array to be processed into a delimited string
 */
function delimitValues(arr) {
	let retval     = '';
	let firstValue = true;

	for (var i = 0, item; item = arr[i]; i++ ) {
		if (firstValue) {
			firstValue = false;
		} else {
			retval += linkbuildrDataDelimiter;
		}
		retval += item;
	}

	return retval;
}

/**
 * Parses the index number out of the index area of the name
 * ie - name_of_input[2] - will parse out the '2'
 * In the event of a double indexed inputnam the return is an object with keys of 'row' & 'col'
 * The first index is 'row' the second is 'col'
 *
 * @param {string} inputName - the name of the input
 */
function parseInputNameKey(inputName) {
	let firstOpenBracketIndex = inputName.indexOf( '[' );
	let lastOpenBracketIndex  = inputName.lastIndexOf( '[' );

	if ( firstOpenBracketIndex == lastOpenBracketIndex ) {
		return inputName.substring( inputName.indexOf( '[' ) + 1, inputName.indexOf( ']' ) );
	} else {
		let rowNumber = inputName.substring( inputName.indexOf( '[' ) + 1, inputName.indexOf( ']' ) );
		let colNumber = inputName.substring( inputName.lastIndexOf( '[' ) + 1, inputName.lastIndexOf( ']' ) );
		return {
			row: rowNumber,
			col: colNumber
		}
	}
}

/**
 * Capitalizes the first letter of a string
 *
 * @param {string} string - any string to have first letter capitalized
 */
function capitalizeFirstLetter(string) {
	return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
}

/**
 * Clears all Dynamic Content so outdated content does not persist in the event of context change.
 */
function clearDynamicContent() {
	document.getElementById( 'header-holder' ).innerHTML = '';
	document.getElementById( 'header-holder' ).parentElement.classList.add( 'hide' );
	document.getElementById( 'file-data-errors' ).innerHTML       = '';
	document.getElementById( 'file-data-table-output' ).innerHTML = '';
	document.getElementById( 'file-data-summary' ).innerHTML      = '';
	document.getElementById( 'file-details' ).innerHTML           = '';

	let lbMessageNoticeContents = document.getElementById( 'lb-message-notice-container' );
	if ( lbMessageNoticeContents ) {
		lbMessageNoticeContents.innerHTML = '';
	}

	let fileReqNotices = document.getElementsByClassName( 'lb-file-requirement-notice' );

	for (var i = 0, elem; elem = fileReqNotices[i]; i++) {
		elem.classList.remove( 'lb-req-failed' );
		elem.classList.remove( 'lb-req-passed' );
	}

	document.getElementById( 'lb-file-requirements' ).classList.remove( 'lb-file-invalid' );
	document.getElementById( 'lb-file-requirements' ).classList.remove( 'lb-file-valid' );

	document.getElementById( 'lb-submit-row' ).classList.add( 'lb-importer-submit-row' );
	document.getElementById( 'lb-submit-import' ).value = false;
}

/**
 * Debounce function, to minimize redundant function calls on form change
 *
 * @param {function} func - the function to debounce
 * @param {int} wait - how long the debounce is for, in ms
 * @param {boolean} immediate - whether to immediately fire the function or not
 */
function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later   = function() {
			timeout = null;
			if ( ! immediate) {
				func.apply( context, args );
			}
		};
		var callNow = immediate && ! timeout;
		clearTimeout( timeout );
		timeout = setTimeout( later, wait );
		if (callNow) {
			func.apply( context, args );
		}
	};
};
