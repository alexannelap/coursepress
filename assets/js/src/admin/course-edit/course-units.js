/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseUnits', function( $, doc, win ) {
        var UnitCollection, UnitList, UnitView;

        UnitCollection = Backbone.Collection.extend({
            url: win._coursepress.ajaxurl + '?action=coursepress_get_course_units&_wpnonce=' + win._coursepress._wpnonce,
            initialize: function( courseId ) {
                this.url += '&course_id=' + courseId;
                this.on( 'error', this.serverError, this );
                this.fetch();
            },
            parse: function( response ) {
                return response.data;
            },
            serverError: function() {
                // @todo: show server error
            }
        });

        UnitList = CoursePress.View.extend({
            template_id: 'coursepress-unit-list-tpl',
            className: 'unit-list-menu',
            events: {
                'click li': 'editUnit'
            },
            initialize: function( model ) {
                this.model = model;
                CoursePress.Events.on( 'coursepress:change_unit_title', this.updateTitle, this );
                this.render();
            },
            editUnit: function( ev ) {
                var sender, unit_id, unit, view, controller;

                sender = this.$(ev.currentTarget);
                controller = this.model.controller;
                unit_id = sender.data('unit');
                unit = this.model.units[unit_id].model;
                view = new CoursePress.UnitDetails(unit, controller);

                controller.$el.html('');
                view.$el.appendTo(controller.$el);
                sender.addClass('active');
                sender.siblings().removeClass('active');
            },
            updateTitle: function( title, unit_id ) {
                var item = this.$('[data-unit="' + unit_id + '"] .unit-title');
                item.html(title);
            }
        });

        UnitView = CoursePress.View.extend({
            template_id: 'coursepress-unit-tpl',
            className: 'unit-view',
            events: {
                'click .cp-unit-heading label': 'toggleListing',
                'click [data-unit]': 'editUnit'
            },
            initialize: function( model, unitsView ) {
                this.model = model;
                this.unitsView = unitsView;
                this.render();
            },
            toggleListing: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    list = sender.parent().next('.cp-unit-content'),
                    is_open = list.is(':visible');

                if ( is_open ) {
                    list.slideUp();
                    sender.addClass('close');
                } else {
                    list.slideDown();
                    sender.removeClass('close');
                }
            },
            editUnit: function() {
                //var sender = this.$(ev.currentTarget),
                //    unit_id = sender.data('unit');
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-units-tpl',
            el: $('#course-units'),
            with_modules: false,
            courseId: 0,
            editCourse: false,
            courseModel: false,
            units: {},
            view: 'unit-list',
            initialize: function( courseModel, EditCourse ) {
                this.with_modules = EditCourse.model.get('with_modules');
                this.courseId = courseModel.get('ID');
                this.model = courseModel;
                this.editCourse = EditCourse;
                this.unitCollection = new UnitCollection(this.courseId);
                this.unitCollection.on( 'update', this.setUnitList, this );
                this.editCourse.on( 'coursepress:load-step-course-units', this.resetView, this );
                this.on( 'view_rendered', this.setUnitListView, this );
                this.render();
            },
            setUnitList: function( collection ) {
                var unitsData, with_modules;

                unitsData = {};
                with_modules = this.editCourse.model.get('with_modules');

                _.each( collection.models, function( model ) {
                    var id, count;

                    id = model.get('ID');
                    count = with_modules ? model.get('modules') : model.get('steps');
                    count = _.keys(count);
                    unitsData[id] = {
                        title: model.get( 'post_title' ),
                        count: count.length,
                        model: model
                    };
                    this.units[id] = model.toJSON();

                }, this );

                this.unitList = new UnitList({units: unitsData, controller: this});
                this.unitList.$el.appendTo( this.editCourse.current );
                this.setUnitListView();
            },
            resetView: function() {
                this.model = this.editCourse.model.toJSON();
                this.$el.html('');
                this.render();
            },
            setUnitListView: function() {
                var count, unitView;

                if ( 'unit-list' === this.view ) {
                    count = _.keys(this.units);

                    if ( count.length ) {
                        _.each(this.units, function (unit) {
                            unitView = new UnitView(unit, this);
                            unitView.$el.appendTo(this.$('#units-container'));
                        }, this);
                    }
                }
            }
        });
    });
})();