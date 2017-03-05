angular.module('routeApp').controller('MainController', ['$scope', '$http', '$location', '$sce', 'Ajax', 'Upload',
    function($scope, $http, $location, $sce, Ajax, Upload){
        $scope.nbSnippets = 0;
        $scope.tree = [];
        $scope.nodes = [];
        $scope.userName = "";
        $scope.userEmail ="";
        $scope.pprofil= "";
        Ajax.csrf().then(function (promise) {
            Ajax.csrfToken = Upload.csrfToken = promise.data;
            $scope.isDisconnectable = false;

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
            } ;
            Upload.getCodes().then(function (promise) {
                if(promise.data.success) {
                    $scope.tree = promise.data.codes;
                    console.log($scope.tree);
                    $scope.nodes = promise.data.nodes;
                    $scope.nbSnippets = $scope.tree.length;

                } else {
                    $location.path('login');
                    return false;
                }
            });
        }, function (error) {
            Ajax.onError(error)
        });
    }
]);
