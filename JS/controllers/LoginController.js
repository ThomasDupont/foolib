angular.module('foolib').controller('LoginController', [
    '$scope',
    '$routeParams',
    '$location',
    'Ajax',
    'Upload',
    'mainFactory',
    function($scope, $routeParams, $location, Ajax, Upload, mainFactory){
        $scope.showpwd          = false;
        $scope.pwdforgetvar     = "";
        $scope.loginStyle       = new styleLogin();
        $scope.usernameRequired = false;
        $scope.passwordRequired = false;
        $scope.emailRequired    = false;

        var testEmail           = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
        mainFactory.viewClass   = 'login';

        Ajax.csrf().then(function (promise) {
            Ajax.csrfToken = Upload.csrfToken = promise.data;
            $("#spn_hol").fadeOut(1000);
        });

        $scope.login = function () {
            var type = 'login';
            if($scope.username == "" || typeof($scope.username) == 'undefined') {
                $scope.usernameRequired = true;
                return false;
            } else if ($scope.password == "" || typeof($scope.password) == 'undefined') {
                $scope.passwordRequired = true;
                return false;
            } else {
                $scope.usernameRequired = false;
                $scope.passwordRequired = false;
                type = (testEmail.test($scope.username)) ? 'email' : 'login';
            }

            Ajax.login($scope.username, $scope.password, type).then(
                function(promise){
                    if(promise.data.success) {
                        mainFactory.isDisconnectable = true;
                        mainFactory.userName         = promise.data.result.name;
                        mainFactory.pprofil          = USERDIR+promise.data.result.pp;
                        mainFactory.crypt            = promise.data.result.crypt;
                        localStorage.setItem(STORAGE, promise.data.result.crypt);
                        location.replace('/');
                    } else {
                        alert("Your credentials aren't recognize");
                        $scope.PostDataResponse = promise.data.message;
                    }
                }
            );
        };

        $scope.register = function () {
            if($scope.username == "" || typeof($scope.username) == 'undefined') {
                $scope.usernameRequired = true;
                return false;
            } else if ($scope.password == "" || typeof($scope.password) == 'undefined') {
                $scope.passwordRequired = true;
                return false;
            } else if ($scope.email == "" || typeof($scope.email) == 'undefined') {
                $scope.emailRequired = true;
                return false;
            } else if($scope.password.length < 6) {
                alert('The password is too short (6 chars minimum)');
                return false;
            } else {
                $scope.usernameRequired = false;
                $scope.passwordRequired = false;
                $scope.emailRequired = false;
                if (!testEmail.test($scope.email)) {
                    alert("The email format is not good");
                    return false;
                }
            }

            Ajax.register($scope.username, $scope.email, $scope.password).then(
                function(promise){
                    if(promise.data.success) {
                        mainFactory.isDisconnectable = true;
                        mainFactory.userName         = $scope.username;
                        mainFactory.userEmail        = $scope.email;
                        mainFactory.userFolder       = promise.data.result.path;
                        mainFactory.userFolderId     = promise.data.result.nodeId;
                        mainFactory.crypt            = promise.data.result.crypt;
                        localStorage.setItem(STORAGE, promise.data.result.crypt);

                        window.location.assign('/');
                    } else {
                        alert(promise.data.message);
                        $scope.PostDataResponse = "An error occured with the account setting";
                    }
                }
            );
        };
        $scope.pwdForgot = function () {
            $scope.showpwd = !$scope.showpwd;
        };
        $scope.sendNewPwd = function() {
            if (!testEmail.test($scope.pwdforgetvar)) {
                alert("The email format is not good");
                return false;
            }
            Ajax.sendemail({email: $scope.pwdforgetvar, login:"#"}, 2).then(function (promise) {
                if(promise.data.success) {
                    alert('A email is sending to you');
                } else {
                    alert('An error occured: '+promise.data.message);
                }
            });
        };
        $scope.gotToTerms = function() {
            $location.path('terms');
        }
    }
]);
