jQuery( function($) {
	$( ".siteContactTableWrapper .wp-list-table > tbody, .emailTemplateWrapper .wp-list-table > tbody" ).scroll(function(e) {
		let parent = $( e.target.parentElement );

		parent.children( "thead" )[0].scrollLeft = e.target.scrollLeft;
		parent.children( "tfoot" )[0].scrollLeft = e.target.scrollLeft;
	});

	$( ".siteContactTableWrapper .wp-list-table > thead, .emailTemplateWrapper .wp-list-table > thead" ).scroll( function(e) {
		let parent = $( e.target.parentElement );

		parent.children( "tbody" )[0].scrollLeft = e.target.scrollLeft;
		parent.children( "tfoot" )[0].scrollLeft = e.target.scrollLeft;
	});

	$( ".siteContactTableWrapper .wp-list-table > tfoot, .emailTemplateWrapper .wp-list-table > tfoot" ).scroll( function(e) {
		let parent = $( e.target.parentElement );

		parent.children( "thead" )[0].scrollLeft = e.target.scrollLeft;
		parent.children( "tbody" )[0].scrollLeft = e.target.scrollLeft;
	});

	$( '.lb-form-element .input-group input, .lb-form-element .input-group textarea' ).focusout( function() {
		var text_val = $( this ).val();
		if ( "" === text_val ) {
			$( this ).removeClass( 'has-value' );
		} else {
			$( this ).addClass( 'has-value' );
		}
	});

	$( '.lb-close-modal' ).click( function() {
		self.parent.tb_remove();
	});

	$( 'body' ).on( 'thickbox:removed', function() {
		if ( wp.data.select( 'core/editor' ) ) {
			let postId = wp.data.select( 'core/editor' ).getCurrentPostId();

			let postNotificationData = { showCount: -1, postSiteContact: {} };
			if ( postId ) {
				LinkbuildrUpdatePostNotificationData( postId )
				.then( () => {
					postNotificationData = wp.data.select( 'linkbuildr' ).receivePostNoficationData( null, postId );
					LinkbuildrHandleNotice( postNotificationData );
				});
			}
		} else {
			location.reload();
		}
	});
});