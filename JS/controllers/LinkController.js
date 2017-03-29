angular.module('routeApp').controller('LinkController', ['$scope', '$routeParams', '$http', '$location', 'Ajax', 'Upload',
    function($scope, $routeParams, $http, $location, Ajax, Upload){
        //?type=confirm&token=token
        if($routeParams.type === undefined) {
            alert("Cette opération n'est pas permise");
            return false;
        }
        $scope.mailConfirm = function(routeParams) {
            if(routeParams.token !== undefined) {
                //attend la fin de l'ajax de récupération du CSRF
                setTimeout(function () {
                    Ajax.confirmMail(routeParams.token).then(function(promise) {
                        if(promise.data.success) {
                            $scope.titleOperation = "Votre email a été validé";
                        } else {

                            $scope.titleOperation = promise.data.message;
                        }
                    });
                }, 1000);
            } else {
                alert("Cette opération n'est pas permise");
                return false;
            }
        };
        $scope.pwdForget = function(routeParams) {
            if(routeParams.token !== undefined) {
                //action
            } else {
                alert("Cette opération n'est pas permise");
                return false;
            }
        }
        switch($routeParams.type) {
            case 'confirm':
                $scope.mailConfirm($routeParams);
                break;
            case 'forget':
                $scope.pwdForget($routeParams);
                break;
            default:
                alert("Vous ne pouvez pas acceder à cette page");
                break;
        }
    }
]);
