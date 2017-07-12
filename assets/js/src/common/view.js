/* global CoursePress, _, Backbone */

(function(){
    'use strict';

    CoursePress.Define('View', function ( $, doc, win ) {
        _.mixin({
            isTrue: function (value, selected) {
                if (_.isArray(selected) ) {
                    return _.contains(selected, value);
                } else if (_.isObject(selected ) ) {
                    return !!selected[value];
                } else {
                    if ( _.isBoolean( value ) && ! _.isBoolean(selected) ) {
                        selected = parseInt(selected, 10) > 0 ? true : false;
                    }
                    return value === selected;
                }
            },
            checked: function (value, selected) {
                return _.isTrue(value, selected) ? 'checked="checked"' : '';
            },
            selected: function (value, selected) {
                return _.isTrue(value, selected) ? 'selected="selected"' : '';
            },
            _getTemplate: function (template_id, data) {
                var settings = {
                        evaluate: /<#([\s\S]+?)#>/g,
                        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                        escape: /\{\{([^\}]+?)\}\}(?!\})/g
                    },
                    tpl = $('#' + template_id);

                if ( tpl.length ) {
                    tpl = _.template( tpl.html(), null, settings);
                }

                return tpl(data);
            }
        });

        return Backbone.View.extend({
            template_id: '',
            model: {},
            events: {
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker'
            },
            initialize: function () {
                if (arguments && arguments[0]) {
                    this.model = new CoursePress.Request(arguments[0]);
                }
                this.render();
            },
            render: function () {
                if ( ! _.isEmpty(this.template_id) ) {
                    var data = !!this.model.get ? this.model.toJSON() : this.model;
                    this.$el.html(_._getTemplate(this.template_id, data));
                }

                this.trigger( 'view_rendered' );

                /**
                 * Trigger whenever the view template is loaded
                 */
                CoursePress.Events.trigger('coursepress:view_rendered', this);

                return this;
            },
            updateModel: function(ev) {
                var input, name, type, value;

                input = $(ev.currentTarget);
                name = input.attr('name');

                if ( ( type = input.attr('type') ) &&
                    _.contains(['checkbox', 'radio'], type ) ) {
                    value = input.is(':checked') ? input.val() : false;
                } else {
                    value = input.val();
                }

                if ( !!this.model.get ) {
                    this.model.set(name, value);
                } else {
                    this.model[name] = value;
                }
            },
            removeErrorMarker: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    error = sender.parents('.cp-error');

                if ( error.length ) {
                    error.removeClass('cp-error');
                }
            },
            visualEditor: function( options ) {
                var id, container, tpl, tpl_id, settings, mceinit, qtinit, editor,
                    content;

                id = options.id;
                container = options.container;
                content = options.content;

                if ( win.tinyMCEPreInit ) {
                    mceinit = win.tinyMCEPreInit.mceInit['coursepress_editor'];
                    qtinit = win.tinyMCEPreInit.qtInit['coursepress_editor'];
                }

                tpl_id = 'coursepress-visual-editor';

                tpl = $('#' + tpl_id).html();
                tpl = tpl.replace( /coursepress_editor/g, id );
                settings = {
                    evaluate: /<#([\s\S]+?)#>/g,
                    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                    escape: /\{\{([^\}]+?)\}\}(?!\})/g
                };
                tpl = _.template( tpl, null, settings );
               // data = !!model.get ? model.toJSON() : model;
                //tpl = tpl( data );
                container.html( tpl );

                if ( win.tinymce.get(id) ) {
                    editor = win.tinymce.get(id);
                    editor.destroy();
                }

                mceinit.selector = '#' + id;
                qtinit.id = id;
                win.tinyMCEPreInit.mceInit[id] = mceinit;
                win.tinyMCEPreInit.qtInit[id] = qtinit;

                _.delay(function() {
                    win.tinymce.init(mceinit);
                    win.quicktags(qtinit);

                    editor = win.tinymce.get(id);
                    if ( editor ) {
                        if ( content ) {
                            editor.setContent(content);
                        }

                        // Add on change callback
                        editor.on('change', function () {
                            content = editor.getContent();

                            if (options.callback) {
                                options.callback.call(null, content);
                            }
                        });
                    }
                    container.find( 'textarea#' + id ).val(content).on( 'change', function() {
                        content = $(this).val();
                        if ( options.callback ) {
                            options.callback.call(null, content);
                        }
                    });

                }, 100 );

            },
            setEditor: function( editor_id ) {
                var self = this;

                if ( win.tinyMCE && win.tinyMCE.get( editor_id ) ) {
                    this._setEditor( editor_id );
                } else {
                    this.$('#wp-' + editor_id + '-wrap .switch-tmce' ).one( 'click', function() {
                        _.delay(function() {
                            self._setEditor(editor_id);
                        }, 100 );
                    });
                }
            },
            _setEditor: function( editor_id ) {
                var content, textarea, self;

                self = this;

                if ( win.tinyMCE && win.tinyMCE.get( editor_id ) ) {
                    var editor = win.tinyMCE.get( editor_id );
                    editor.on( 'change', function() {
                        content = editor.getContent();
                        textarea = self.$('#' + editor_id );
                        textarea.val( content );
                        textarea.trigger('change');
                    });
                }
            }
        });
    });
})();