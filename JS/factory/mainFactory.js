angular.module('foolib').factory('mainFactory',
    function(Ajax, Upload, $http, $location, $sce, $q) {

        var factory = {
            nbSnippets          : 0,
            tree                : [],
            nodes               : [],
            userName            : "",
            userEmail           : "",
            pprofil             : USERDIR+"default.png",
            isDisconnectable    : false,
            passByMain          : true,
            viewClass           : 'container',
            crypt               : localStorage.getItem(STORAGE) || null,
            style_general       : new style_general(),

            /**
             * [description]
             * @return {[type]} [description]
             */
            init: function() {
                return $q(function(resolve, reject) {
                    Ajax.csrf().then(function (promise) {
                        Ajax.csrfToken = Upload.csrfToken = promise.data;
                        setTimeout(function() {
                            $("#spn_hol").fadeOut(1000);
                        }, 0);
                            factory.checkUser();
                            Upload.getCodes().then(function (promise) {
                                if(promise.data.success) {
                                    factory.tree        = promise.data.codes;
                                    factory.nodes       = promise.data.nodes;
                                    factory.nbSnippets  = factory.tree.length;

                                    var nodeID;
                                    for(var i=0, nodes = factory.nodes; i<nodes.length; i++) {
                                        if(nodes[i].parentNode_ID === 0) {
                                            nodeId = nodes[i].node_ID;
                                        }
                                    }
                                    factory.parentNodeID = nodeId;
                                    resolve();

                                } else {
                                    $location.path('login');
                                    return false;
                                }
                            });
                    });
                });
            },
            /**
             * [description]
             */
            checkUser: function () {
                var c = factory.crypt;
                Ajax.checkUser(c).then(function (promise) {
                    if(promise.data.success) {
                        factory.isDisconnectable = true;
                        factory.userName         = promise.data.name;
                        factory.userEmail        = promise.data.email;
                        factory.pprofil          = (promise.data.pp == "") ?
                                                 USERDIR+"default.png" :
                                                 USERDIR+promise.data.pp;
                    } else {
                        factory.checkUserProcess = false;
                        $location.path('login');
                    }
                });
            },
            /**
             * [disconnect description]
             * @return {[type]} [description]
             */
            disconnect: function disconnect () {
                Ajax.disconnect();
                factory.isDisconnectable = false;
                factory.tree             = "";
                factory.crypt            = null;
                factory.userName         = "";
                factory.userEmail        = "";
                factory.pprofil          = USERDIR+"default.png";
                factory.nodes            = "";
                factory.nbSnippets       = 0;
                factory.passByMain       = false;
                factory.viewClass        = 'login';
                localStorage.removeItem(STORAGE);
            },
            /**
            * Trigger function
            * @param {bool} listener, the action for waiting, send true to juste have a time out
            * @param {object} function, the callback function
            * @param {object} scope, the angular scope of the evant
            * @param {int} waiting, the time to wait in millisecond (optionnal)
            */
            triggerFunction: function(listener, funct, scope, waiting) {
                var $wait = waiting || 100;
                var $funct = funct, $listener = listener, _scope = scope;
                setTimeout(function() {
                    if(!$listener) {
                        factory.triggerFunction($listener, $funct, _scope);
                    } else {
                        _scope.$apply(function() {
                            $funct();
                        });
                    }
                }, $wait);
            }
        };

        return factory;
    }
);
