angular.module('routeApp').controller('MainController', ['$scope', '$http', '$q', '$timeout', '$location', '$sce', 'Ajax', 'Upload',
    function($scope, $http, $q, $timeout, $location, $sce, Ajax, Upload){
        $scope.nbSnippets = 0;
        $scope.tree = [];
        $scope.nodes = [];
        $scope.userName = "";
        $scope.userEmail ="";
        $scope.pprofil= "";
        $scope.isDisconnectable = false;
        $scope.passByMain = true;

        Ajax.csrf().then(function (promise) {
            Ajax.csrfToken = Upload.csrfToken = promise.data;


            //$timeout(Ajax.test(), 100);

            Ajax.checkUser().then(function (promise) {

                if(promise.data.success) {
                    $scope.isDisconnectable = true;
                    $scope.userName = promise.data.name;
                    $scope.userEmail = promise.data.email;
                    $scope.pprofil = (promise.data.pp == "") ? USERDIR+"default.png" : USERDIR+promise.data.pp;
                } else {
                    $location.path('login');
                }
            });
            $scope.disconnect = function disconnect () {
                Ajax.disconnect();
                $scope.isDisconnectable = false;
                $scope.userName = "";
                $scope.userEmail ="";
                $scope.pprofil= "";
                $scope.passByMain = false;
            } ;
            Upload.getCodes().then(function (promise) {
                if(promise.data.success) {
                    $scope.tree = promise.data.codes;
                    $scope.nodes = promise.data.nodes;
                    $scope.nbSnippets = $scope.tree.length;

                    var nodeID;
                    for(var i=0, nodes = $scope.nodes; i<nodes.length; i++) {
                        if(nodes[i].parentNode_ID == 0) {
                            nodeId = nodes[i].node_ID;
                        }
                    }
                    $scope.parentNodeID = nodeId;


                } else {
                    $location.path('login');
                    return false;
                }
            });
        }, function (error) {
            Ajax.onError(error)
        });

        $scope.inArray = function (needle, haystack) {
            for(var i = 0; i < haystack.length; i++) {
                if(haystack[i] == needle) return true;
            }
            return false;
        }
    }
]);
