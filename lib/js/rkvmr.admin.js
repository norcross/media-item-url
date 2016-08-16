//******************************************************************************
// now start the engine
//******************************************************************************
jQuery(document).ready( function($) {

//******************************************************************************
// Set some vars for later.
//******************************************************************************
	var editLink;
	var editRow;

//******************************************************************************
// Hide any existing media box URLs on load.
//******************************************************************************
	$( 'table.media td.title div.row-actions' ).each( function() {
		$( this ).find( 'div.media-url-box' ).hide();
	});

//******************************************************************************
// Show our inputs on click.
//******************************************************************************
	$( 'span.media-url' ).on( 'click', 'a.media-url-click', function( event ) {

		// This removes the hash in the URL for cleaner UI.
		event.preventDefault();

		// Stop the propagation.
		event.stopPropagation();

		// Determine our clicked item.
		editLink = $( this );

		// Target the specific row we are editing.
		editRow = editLink.next( 'div.media-url-box' );

		// Add a new class.
		editLink.toggleClass( 'media-url-open' );

		// Add a class to the row itself.
		editRow.toggleClass( 'media-url-visible' );

		// Show my edit fields.
		editRow.slideToggle( 'slow' );

		// Hide the rest.
		$( 'div.media-url-box' ).not( editRow ).slideUp( 'slow' ).removeClass( 'media-url-visible' );
	});

//******************************************************************************
// select text on click
//******************************************************************************
	$( 'input.media-url-field' ).focus( function() {
		this.select();
	});

//******************************************************************************
// that's all folks. we're done here
//******************************************************************************
});
