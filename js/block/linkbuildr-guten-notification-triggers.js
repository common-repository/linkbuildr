const { findIndex } = lodash;

const LBnoticeId        = 'lbPostContactDataNotice';
const LBpublishNoticeId = 'lbSentEmailOnPublishNotice';

let LBPostEmailsNotSent = [];
let PostJustPublished   = false;

let wasSavingPost     = wp.data.select( 'core/editor' ).isSavingPost();
let wasAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
let wasPostPublished  = wp.data.select( 'core/editor' ).isCurrentPostPublished();

let postNotificationData = { displayNotifications: false, showCount: -1, postSiteContactInvalid: [] };
wp.data.subscribe(
	function() {

			const isSavingPost     = wp.data.select( 'core/editor' ).isSavingPost();
			const isAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
			const isPostPublished  = wp.data.select( 'core/editor' ).isCurrentPostPublished();

			const shouldTriggerNotice = (
			( wasSavingPost && ! isSavingPost ) ||
			( wasAutosavingPost && ! isAutosavingPost ) ||
			( ! wasPostPublished && isPostPublished )
			);

			const shouldTriggerPublishNotice = ( ! wasPostPublished && isPostPublished );

			wasSavingPost     = isSavingPost;
			wasAutosavingPost = isAutosavingPost;
			wasPostPublished  = isPostPublished;

		if ( shouldTriggerNotice ) {
			let postId = wp.data.select( 'core/editor' ).getCurrentPostId();

			if (postId) {
				LinkbuildrUpdatePostNotificationData( postId )
				.then(
					() => {
						postNotificationData = wp.data.select( 'linkbuildr' ).receivePostNoficationData( null, postId );
						if (PostJustPublished) {
							LinkbuildrHandlePublishNotice( postNotificationData );
							PostJustPublished = false;
						} else {
							LBPostEmailsNotSent = getUnsentContacts( postNotificationData.postSiteContact );
							if ( shouldTriggerPublishNotice ) {
								PostJustPublished = true;
							}
						}
						LinkbuildrHandleNotice( postNotificationData );
					}
				);
			}
		}
	}
);

wp.domReady(
	function() {
		updatedPostNotificationData();
	}
);

function LinkbuildrUpdatePostNotificationData(postId){
	return apiFetch( { path: 'linkbuildr/v1/postNotificationData/' + postId } ).then(
		notificationData => {
        linkBuildrNotificationStore.dispatch( linkbuildrNotificationActions.setPostNoficationData( notificationData ) );
		}
	)
}

function IndexOfLBNotice() {
	let currentNotices  = wp.data.select( 'core/notices' ).getNotices();
	let indexOfLBNotice = findIndex( currentNotices, function(notice) { return notice.id == LBnoticeId; } );
	return indexOfLBNotice;
}

function IndexOfLBPublishNotice() {
	let currentNotices  = wp.data.select( 'core/notices' ).getNotices();
	let indexOfLBNotice = findIndex( currentNotices, function(notice) { return notice.id == LBpublishNoticeId; } );
	return indexOfLBNotice;
}

function getInvalidContacts( postSiteContact ) {
	let invalidContacts = [];
	if ( postSiteContact.length > 0 ) {
		postSiteContact.forEach(
			function(psc) {
				if (psc.is_valid == 'false') {
					invalidContacts.push( psc );
				}
			}
		);
	}

	return invalidContacts;
}

function getUnsentContacts( postSiteContact ) {
	let unsentContacts = [];
	if ( postSiteContact.length > 0 ) {
		postSiteContact.forEach(
			function(psc) {
				if (psc.is_sent == 'false') {
					unsentContacts.push( psc );
				}
			}
		);
	}

	return unsentContacts;
}

function LinkbuildrHandlePublishNotice(postNotificationData) {
	if (IndexOfLBPublishNotice() > -1) {
		wp.data.dispatch( 'core/notices' ).removeNotice( LBpublishNoticeId );
	}

	let sentEmailCount = 0;
	if ( 'postSiteContact' in postNotificationData && postNotificationData.postSiteContact.length > 0 ) {
		postNotificationData.postSiteContact.forEach(
			function(psc) {
				if ( psc.is_sent == 'true' ) {
					LBPostEmailsNotSent.forEach(
						function(lbPrePublishPSC) {
							if (lbPrePublishPSC.id === psc.id) {
								if ( lbPrePublishPSC.is_sent == 'false' && psc.is_sent == 'true' ) {
									sentEmailCount++;
								}
							}
						}
					);
				}
			}
		);
	}

	if ( sentEmailCount > 0 ) {
		wp.data.dispatch( 'core/notices' ).createNotice(
			'success lb-notice-success',
			sentEmailCount + ((sentEmailCount == 1) ? ' outreach email sent.' : ' outreach emails sent.'),
			{
				id: LBpublishNoticeId,
				isDismissible: true,
			}
		).then(
			( ) => {
            let lbSuccessNotice = document.querySelector( '.lb-notice-success' );
            if (lbSuccessNotice) {
					let noticeContent = document.querySelector( '.lb-notice-success .components-notice__content' );
					if (noticeContent) {
						if ( 0 > noticeContent.innerHTML.indexOf( 'lb-logo-notice-span' ) ) {
							noticeContent.innerHTML = '<span class="lb-logo-notice-span"></span><span class="lb-notice-content">' + noticeContent.innerHTML + '</span>';
						}
					}
				}
			}
		);
	}
}

function LinkbuildrHandleNotice(postNotificationData) {
	if (IndexOfLBNotice() > -1) {
		wp.data.dispatch( 'core/notices' ).removeNotice( LBnoticeId );
	}

	if (postNotificationData.showCount > 0 && postNotificationData.displayNotifications) {
		let invalidContact = getInvalidContacts( postNotificationData.postSiteContact );
		wp.data.dispatch( 'core/notices' ).createNotice(
			'info lb-notice',
			postNotificationData.showCount + ((postNotificationData.showCount == 1) ? ' website in your post needs' : ' websites in your post need') + ' contact details added:', // Text string to display.
			{
				id: LBnoticeId,
				isDismissible: true,
				actions: [
					{
						url: '/wp-admin/admin.php?page=site-contact-form&id=' + invalidContact[0].id +
								'&scid=' + invalidContact[0].site_contact_id +
								'&nb=1&pid=' + invalidContact[0].post_id +
								'&bid=' + invalidContact[0].blog_id + '&TB_iframe=true',
						label: 'Add Details',
						className: 'thickbox'
					}
				]
			}
		).then(
			( ) => {
            let noticeLink = document.querySelector( '.lb-notice .components-notice__content a' );
            if (noticeLink) {
					if (noticeLink.className.indexOf( 'thickbox' ) < 0) {
						noticeLink.className += ' thickbox';
					}

					let noticeContent = document.querySelector( '.lb-notice .components-notice__content' );
					if (noticeContent) {
						if ( 0 > noticeContent.innerHTML.indexOf( 'lb-logo-notice-span' ) ) {
							noticeContent.innerHTML = '<span class="lb-logo-notice-span"></span><span class="lb-notice-content">' + noticeContent.innerHTML + '</span>';
						}
					}
				}
			}
		);
	}
}

function updatedPostNotificationData() {
	let postId = wp.data.select( 'core/editor' ).getCurrentPostId();

	if (postId) {
		LinkbuildrUpdatePostNotificationData( postId )
		.then(
			() => {
				postNotificationData = wp.data.select( 'linkbuildr' ).receivePostNoficationData( null, postId );
				LBPostEmailsNotSent  = getUnsentContacts( postNotificationData.postSiteContact );
			}
		);
	}
}
