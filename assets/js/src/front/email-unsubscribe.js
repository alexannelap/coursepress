/* global CoursePress */

(function() {
    'use strict';

    // Email unsubscribe confirmation popup.
    CoursePress.Define( 'EmailUnsubscribeModal', function ( $ ) {

        var EmailUnsubscribeModal = CoursePress.View.extend( {
            template_id: 'cp-unsubscribe-message',
            className: 'coursepress-modal-front',
            events: {
                'click .cp-close': 'remove',
            },
            initialize: function () {

                if ( $( '#cp-unsubscribe-message' ).length > 0 ) {
                    this.render();
                }
            },

            // On render.
            render: function() {
                CoursePress.View.prototype.render.apply( this );
                this.$el.appendTo( 'body' );
            },
        } );

        new EmailUnsubscribeModal();
    } );
})();