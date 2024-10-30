const { apiFetch } = wp;
const {
	registerStore,
	withSelect,
}                  = wp.data;

const DEFAULT_STATE = { notificationData: {}, };

const linkbuildrNotificationActions = {
	setPostNoficationData( notificationData ) {
		return {
			type: 'SET_POST_NOTIFICATION_DATA',
			notificationData,
		};
	},
	fetchPostNotificationData( path ) {
		return {
			type: 'FETCH_POST_NOTIFICATION_DATA',
			path,
		};
	},
	receivePostNoficationData( path ) {
		return {
			type: 'RECEIVE_POST_NOTIFICATION_DATA',
			path,
		};
	},
};

const linkBuildrNotificationStore = registerStore(
	'linkbuildr',
	{
		reducer( state            = DEFAULT_STATE, action ) {

			switch ( action.type ) {
				case 'SET_POST_NOTIFICATION_DATA':
					return {
						...state,
						notificationData: action.notificationData,
				};
			}

			return state;
		},

		linkbuildrNotificationActions,

		selectors: {
			receivePostNoficationData( state, postId ) {
				const { notificationData } = state;
				return notificationData;
			},
		},

		controls: {
			RECEIVE_POST_NOTIFICATION_DATA( action ) {
				return apiFetch( { path: action.path } );
			},
			FETCH_POST_NOTIFICATION_DATA( action ) {
				return apiFetch( { path: action.path } );
			},
		},

		resolvers: {
			* receivePostNoficationData( state, postId ) {
				const notificationData = yield linkbuildrNotificationActions.fetchPostNotificationData( 'linkbuildr/v1/postNotificationData/' + postId );// + postId );
				return linkbuildrNotificationActions.setPostNoficationData( notificationData );
			},
		},
	}
);
