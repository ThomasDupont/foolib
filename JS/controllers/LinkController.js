angular.module('foolib').controller('LinkController', [
    '$scope',
    '$routeParams',
    '$http',
    '$location',
    'Ajax',
    'Upload',
    'mainFactory',
    function($scope, $routeParams, $http, $location, Ajax, Upload, mainFactory){
        //?type=confirm&token=token

        mainFactory.viewClass = "container link";
        $scope.newpwd = false;
        $scope.newpwdstr = "";
        if($routeParams.type === undefined) {
            alert("You couldn't access to this page");
            $location.path('login');
            return false;
        }

        $scope.csrf = function() {
            $("#spn_hol").fadeOut(1000);
            return Ajax.csrf();

        }
        $scope.mailConfirm = function(routeParams) {
            if(routeParams.token !== undefined) {
                $scope.csrf().then(function (promise) {
                    Ajax.csrfToken = Upload.csrfToken = promise.data;
                    Ajax.confirmMail(routeParams.token).then(function(promise) {
                        if(promise.data.success) {
                            $scope.titleOperation = "Your email has been validated";
                        } else {
                            console.log(promise.data.message);
                            $scope.titleOperation = promise.data.message;
                        }
                    });
                });
            } else {
                alert("This operation is not permit");
                return false;
            }
        };
        $scope.pwdForget = function(routeParams) {
            $scope.csrf().then(function (promise) {
                Ajax.csrfToken = Upload.csrfToken = promise.data;
            });
            if(routeParams.token !== undefined) {
                $scope.newpwd = true;
                $scope.sendNewPwd = function() {
                    if($scope.newpwdstr1 != $scope.newpwdstr2) {
                        alert("The passwords doesn't match each other");
                        return false;
                    }
                    if($scope.newpwdstr1.length < 6) {
                        alert("The passwords is too short (6 chars minimum)");
                        return false;
                    }

                    Ajax.sendNewPwd($scope.newpwdstr1,routeParams.token).then(function (promise) {
                        if(promise.data.success) {
                            alert("You password is now up to date");
                            $location.path('login');
                        } else {
                            alert('On error occured with your new password: '+ promise.data.message)
                        }
                    });
                };
            } else {
                alert("This operation is not permit");
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
                alert("You couldn't access to this page");
                $location.path('login');
                break;
        }
    }
]);
