jQuery( document ).ready( function( $ ) {
	$( '.column-header-icon input[type="radio"]' ).change( function() {
		var container = $( this ).parents( '.input' );
		var val = $( this ).val();

		container.find( '.section' ).hide();
		container.find( '.cpacic-label-icon-' + val ).show();
	} );

	$( '.cpacic-label-icon-attachment a' ).click( function( e ) {
		e.preventDefault();

		var container = $( this ).parents( '.input' );

		// Media uploader
		var uploader;

		uploader = wp.media( {
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false
		} );

		uploader.on( 'select', function() {
			var selection = uploader.state().get( 'selection' ).toJSON();
			var attachment = selection[0];
			
			if ( attachment ) {
				var image_url = attachment.url;

				if ( typeof attachment.sizes.thumbnail !== 'undefined' ) {
					image_url = attachment.sizes.thumbnail.url;
				}

				container.find( 'input[type="hidden"]' ).val( attachment.id );
				container.find( '.cpacic-label-icon-attachment .icon-preview' ).html( '<img src="' + image_url + '" />' );
				container.find( '.no-icon' ).hide();
			}
		} );

		uploader.open();
	} );

	var cpacic_current_column;

	$( '.cpacic-label-icon-dashicon a' ).click( function() {
		var dashicon_current = $( this ).parent().find( 'input' ).val();
		console.log(dashicon_current);
		$( '#cpacic-select-icon .cpacic-dashicon' ).removeClass( 'selected' );
		$( '#cpacic-select-icon .cpacic-dashicon[data-dashicon="' + dashicon_current + '"]' ).addClass( 'selected' );

		cpacic_current_column = $( this ).parents( '.column-form' );
	} );

	$( '.cpacic-dashicon' ).click( function( e ) {
		e.preventDefault();

		var new_icon = $( this ).data( 'dashicon' );

		$( this ).addClass( 'selected' ).siblings().removeClass( 'selected' );
		cpacic_current_column.find( '.cpacic-label-icon-dashicon input' ).val( new_icon );
		cpacic_current_column.find( '.cpapic-current-icon div' ).html( '&#x' + new_icon + ';' );
	} );

	$( '.cpacic-popup-toolbar a' ).click( function( e ) {
		e.preventDefault();

		$( '#TB_closeWindowButton' ).trigger( 'click' );
	} );
} );
