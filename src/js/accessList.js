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

    NS.AccessListWidget = Y.Base.create('AccessListWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance){
            this.reloadList();
        },
        reloadList: function(){
            this.set('waiting', true);
            var filter = this.get('filter');

            this.get('appInstance').accessList(filter, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('accessList', result.accessList);
                }
                this.renderList();
            }, this);
        },
        renderList: function(){
            var accessList = this.get('accessList');
            if (!accessList){
                return;
            }
            var tp = this.template,
                lst = "";

            accessList.each(function(access){

                var varList = "";
                access.get('vars').each(function(accessVar){
                    varList += tp.replace('varItem', {
                        id: accessVar.get('id'),
                        name: accessVar.get('name')
                    });
                }, this);

                lst += tp.replace('row', [
                    {
                        dateline: Brick.dateExt.convert(access.get('dateline')),
                        varList: varList
                    },
                    access.toJSON()
                ]);
            });

            tp.setHTML('list', tp.replace('table', {rows: lst}));
        },
        filterSet: function(){
            var tp = this.template,
                filter = this.get('filter');

            filter.search = tp.getValue('search');
            this.reloadList();
        },
        filterClear: function(){
            this.template.setValue('search', '');
            this.filterSet();
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget,table,row,varItem'},
            accessList: {value: null},
            filter: {
                value: {},
                setter: function(val){
                    return Y.merge({
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