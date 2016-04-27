var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.LogListWidget = Y.Base.create('LogListWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance){
            var tp = this.template,
                filter = this.get('filter');

            tp.setValue({
                level: filter.level
            });

            this.set('waiting', true);
            this.get('appInstance').logOwnerList(function(err, result){

                if (!err){
                    this._renderOwnerList(result.logOwnerList);
                }

                this.filterSet();
            }, this);
        },
        _renderOwnerList: function(list){
            var tp = this.template,
                lst = "";

            for (var i = 0; i < list.length; i++){
                lst += tp.replace('option', {
                    id: list[i],
                    value: list[i]
                });
            }
            tp.setHTML('owner', tp.replace('ownerList', {
                rows: lst
            }));
        },
        reloadList: function(){
            this.set('waiting', true);

            var filter = this.get('filter');

            this.get('appInstance').logList(filter, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('logList', result.logList);
                }
                this.renderList();
            }, this);
        },
        renderList: function(){
            var logList = this.get('logList');
            if (!logList){
                return;
            }
            var tp = this.template,
                lst = "";

            logList.each(function(log){
                lst += tp.replace('row', [
                    {
                        classLevel: function(){
                            switch (log.get('level')) {
                                case 'error':
                                    return 'text-danger';
                            }
                        }(),
                        dateline: Brick.dateExt.convert(log.get('dateline')),
                        debugInfo: function(){
                            var info = log.get('debugInfo');
                            if (info === ''){
                                return ''
                            }

                            var obj = {};
                            try {
                                obj = Y.JSON.parse(info);
                            } catch (e) {
                                return '';
                            }
                            var lst = ""
                            for (var n in obj){
                                lst += tp.replace('infoItem', {
                                    name: n,
                                    value: obj[n]
                                });
                            }
                            return lst;
                        }()
                    },
                    log.toJSON()
                ]);
            });

            tp.setHTML('list', tp.replace('table', {rows: lst}));
        },
        filterSet: function(){
            var tp = this.template,
                filter = this.get('filter');

            filter.level = tp.getValue('level');
            filter.search = tp.getValue('search');
            filter.owner = tp.getValue('ownerList.id');

            this.reloadList();
        },
        filterClear: function(){
            this.template.setValue({
                level: 'debug',
                'ownerList.id': '',
                search: '',
            });
            this.filterSet();
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget,table,row,infoItem,ownerList,option'},
            logList: {value: null},
            filter: {
                value: {},
                setter: function(val){
                    return Y.merge({
                        level: 'debug',
                        search: ''
                    }, val || {});
                }
            }
        },
        CLICKS: {
            filterSet: 'filterSet',
            filterClear: 'filterClear'
        }
    });
};