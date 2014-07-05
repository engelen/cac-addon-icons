jQuery( document ).ready( function( $ ) {
	$( '.column-header-icon input[type="radio"]' ).change( function() {
		var container = $( this ).parents( '.input' );
		var val = $( this ).val();

		if ( val == 'custom' ) {
			container.find( '.cpacic-label-icon-custom' ).show();
			container.find( '.cpacic-label-icon-attachment' ).hide();
		}
		else if ( val == 'attachment' ) {
			container.find( '.cpacic-label-icon-custom' ).hide();
			container.find( '.cpacic-label-icon-attachment' ).show();
		}
		else {
			container.find( '.cpacic-label-icon-custom' ).hide();
			container.find( '.cpacic-label-icon-attachment' ).hide();
		}
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

				container.find( 'input[type="text"]' ).val( attachment.id );
				container.find( '.cpacic-label-icon-attachment .icon-preview' ).html( '<img src="' + image_url + '" />' );
				container.find( '.no-icon' ).hide();
			}
		} );

		uploader.open();
	} );
} );