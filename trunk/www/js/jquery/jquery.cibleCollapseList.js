(function($) {
//plugin name
    var pluginName = "cibleCollapseList";
    var findIndexOfEl = function($el) {

    };
    //default plugins settings here
    var defaultSettings = {
        delegate: '> li',
        collapseClass: 'fonc-collapse-box',
        columnBreakpoints: 3,
        isIE: false
    };
//private properties and methods    
    var members = {
        _$this: null,
        _$delegates: null,
        _transitionning: false,
        _activeIndex: -1,
        _dispatchEvent: function(event, data) {
            this._$this.trigger(event + '.' + pluginName, data);
        },
        _call: function(name, args) {
            this._$this[pluginName](name, args);
        },
        _toggleBoxes: function($el) {
            if (!$el.is('.active')) {
                this._openBoxes($el);
            }
            else {
                this._closeBoxes($el, function() {
                });
            }
        },
        _openBoxes: function($el) {

            var that = this;

            var $collapseBoxes = this._$this.find('.' + this._settings.collapseClass);
            $collapseBoxes.collapse('show');

            $el.addClass('active');
            this._dispatchEvent('activating', {current: $el, collapseBoxes: $collapseBoxes});

            $collapseBoxes.on('shown.bs.collapse', function() {
                that._transitionning = false;
                $collapseBoxes.off('shown.bs.collapse');
                that._dispatchEvent('activated', {current: $el, collapseBoxes: $collapseBoxes});
            });
            if (this._settings.isIE) {
                $collapseBoxes.trigger('shown.bs.collapse');
                
                $collapseBoxes.addClass('in');
            }
        },
        _closeBoxes: function($el, callback) {
            var that = this;
            var $collapseBoxes = this._$this.find('.' + this._settings.collapseClass);
            var collapseBoxesNum = $collapseBoxes.length;
            this._dispatchEvent('desactivating', {current: $el, collapseBoxes: $collapseBoxes});
            if ($collapseBoxes.is('.in')) {
                $collapseBoxes.on('hidden.bs.collapse', function() {
                    collapseBoxesNum--;
                    if (collapseBoxesNum <= 0) {
                        that._transitionning = false;
                        $collapseBoxes.off('hidden.bs.collapse');
                        that._$delegates.removeClass('active');
                        that._dispatchEvent('desactivated', {current: $el, collapseBoxes: $collapseBoxes});
                        callback();
                    }
                });
                if (this._settings.isIE) {
                    $collapseBoxes.trigger('hidden.bs.collapse');
                      $collapseBoxes.removeClass('in');
                }
                $collapseBoxes.collapse('hide');
            }
            else {
                that._$delegates.removeClass('active');
                callback();
            }
        },
        _createMarkup: function($el) {
            var index = $el.index();
            for (var i = 1; i <= this._settings.columnBreakpoints; i++) {
                var indexToCreate = (Math.ceil((index + 1) / i) * i) - 1;
                var existingEl = this._$delegates.eq(indexToCreate).next('.' + this._settings.collapseClass);
                if (existingEl.length > 0) {
                    existingEl.addClass(this._settings.collapseClass + '-' + i);
                }
                else {
                    var $created = $('<div class="collapse ' + this._settings.collapseClass + '"></div>');
                    $created.addClass(this._settings.collapseClass + '-' + i);
                    this._dispatchEvent('generate', {created: $created, current: $el});
                    if (this._$delegates.eq(indexToCreate).length > 0) {
                        this._$delegates.eq(indexToCreate).after($created);
                    }
                    else {
                        this._$this.append($created);
                    }
                }
            }
            this._activeIndex = index;
        },
        _deleteMarkup: function() {
            this._$this.find('.' + this._settings.collapseClass).remove();
        },
        //constructor, sort of
        init: function(obj) {
            //build a new collapse with a slower animation

            //get the original input
            this._$this = $(obj);
            var that = this;
            this._$delegates = this._$this.find(this._settings.delegate);
            this._$delegates.on('click', function() {
                if (!that._transitionning) {
                    var $el = $(this);
                    that._transitionning = true;
                    //check if one is opened, then close it
                    if (that._activeIndex == $(this).index()) {
                        that._toggleBoxes($el);
                    }
                    else if (that._activeIndex >= 0) {
                        that._closeBoxes($el, function() {
                            //delete the markup
                            that._deleteMarkup();
                            //create the markup
                            that._createMarkup($el);
                            //open the boxes
                            that._openBoxes($el);
                        })
                    }
                    else {
                        //create the markup
                        that._createMarkup($el);
                        //open the boxes
                        that._openBoxes($el);
                    }
                    //open the box
                }
            })
        }
    }
//public methods here
    members.methods = {
    };
    //everything below should be left as it is
    $.fn[pluginName] = function(options, args) {
        if (typeof options === "string") {
            var valToRet = null;
            var ret = this.each(function() {
                var data = $(this).data('data' + pluginName);
                if (typeof data === 'object') {
                    if (data.methods[options]) {
                        valToRet = data.methods[options](data, args);
                    }
                }
            });
            return valToRet || ret;
        }
        else {
            return this.each(function() {
                var settings = $.extend({}, defaultSettings, options);
                if (typeof $(this).data('data' + pluginName) === 'undefined') {
                    var dataToSave = Object.create(members);
                    dataToSave._settings = Object.create(settings);
                    $(this).data('data' + pluginName, dataToSave);
                    dataToSave.init(this);
                }
            });
        }
    };
}(jQuery));
if (!Object.create) {
    Object.create = (function() {
        function F() {
        }

        return function(o) {
            if (arguments.length != 1) {
                throw new Error('Object.create implementation only accepts one parameter.');
            }
            F.prototype = o;
            return new F()
        }
    })()
}