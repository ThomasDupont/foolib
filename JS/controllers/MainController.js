angular.module('routeApp').controller('MainController', ['$scope', '$http', '$location', '$sce', 'Ajax', 'Upload',
    function($scope, $http, $location, $sce, Ajax, Upload){
        $scope.userName = "";
        $scope.userEmail ="";
        Ajax.csrf().then(function (promise) {
            Ajax.csrfToken = Upload.csrfToken = promise.data;
            $scope.isDisconnectable = false;

            Ajax.checkUser().then(function (promise) {

                if(promise.data.success) {
                    $scope.isDisconnectable = true;
                    $scope.userName = promise.data.name;
                    $scope.userEmail = promise.data.email;
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
        });


    }
]);
