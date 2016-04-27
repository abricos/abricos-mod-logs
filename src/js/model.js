var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['appModel.js']}
    ]
};
Component.entryPoint = function(NS){
    var Y = Brick.YUI,
        SYS = Brick.mod.sys;

    NS.Log = Y.Base.create('log', SYS.AppModel, [], {
        structureName: 'Log'
    });

    NS.LogList = Y.Base.create('logList', SYS.AppModelList, [], {
        appItem: NS.Log
    });

    NS.Access = Y.Base.create('access', SYS.AppModel, [], {
        structureName: 'Access'
    });

    NS.AccessList = Y.Base.create('accessList', SYS.AppModelList, [], {
        appItem: NS.Access
    });

    NS.AccessVar = Y.Base.create('accessVar', SYS.AppModel, [], {
        structureName: 'AccessVar'
    });

    NS.AccessVarList = Y.Base.create('accessVarList', SYS.AppModelList, [], {
        appItem: NS.AccessVar
    });

    NS.Config = Y.Base.create('config', SYS.AppModel, [], {
        structureName: 'Config'
    });
};
