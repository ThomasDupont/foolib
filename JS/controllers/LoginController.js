angular.module('routeApp').controller('LoginController', ['$scope', '$routeParams', '$location', 'Ajax', 'Upload',
    function($scope, $routeParams, $location, Ajax, Upload){
        $scope.showpwd = false;
        $scope.pwdforgetvar = "";
        $scope.loginStyle = new styleLogin();
        //document.getElementsByTagName('nav')[0].style.display = 'none';
        $scope.usernameRequired = false;
        $scope.passwordRequired = false;
        $scope.emailRequired = false;
        var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
        $scope.$parent.viewClass = 'login';

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
                      $scope.$parent.isDisconnectable = true;
                      $scope.$parent.userName = promise.data.result.name;
                      $scope.$parent.pprofil = USERDIR+promise.data.result.pp;
                      $scope.$parent.crypt = promise.data.result.crypt;
                      localStorage.setItem(STORAGE, promise.data.result.crypt);
                      $scope.$parent.viewClass = 'container';

                      location.replace('/');
                  } else {
                      $scope.PostDataResponse = promise.data.message;
                  }
              }) ;
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
            } else {
                $scope.usernameRequired = false;
                $scope.passwordRequired = false;
                $scope.emailRequired = false;
                if (!testEmail.test($scope.email)) {
                    alert("le format de l'email n'est pas bon");
                    return false;
                }
            }

          //if($scope.password === $scope.passwordConfirm) {
              //document.getElementById('loader').style.display = 'block';
              Ajax.register($scope.username, $scope.email, $scope.password).then(
                  function(promise){
                      if(promise.data.success) {
                          $scope.$parent.isDisconnectable = true;
                          $scope.$parent.userName = $scope.username;
                          $scope.$parent.userEmail = $scope.email;
                          $scope.$parent.userFolder = promise.data.result.path;
                          $scope.$parent.userFolderId = promise.data.result.nodeId;
                          $scope.$parent.crypt = promise.data.result.crypt;
                          localStorage.setItem(STORAGE, promise.data.result.crypt);
                          Ajax.sendemail({email: $scope.email, login: $scope.username}, 1).then(function (promise) {
                               //document.getElementById('loader').style.display = 'none';
                               $scope.$parent.viewClass = 'container';
                               //document.getElementsByTagName('nav')[0].style.display = 'block';
                               $location.path('home');
                          });

                      } else {
                          $scope.PostDataResponse = "Erreur à la création du compte";
                      }
                  });
         // } else {
        //      $scope.PostDataResponse = "Les mots de passe ne correspondent pas";
         // }

      };
      $scope.pwdForgot = function () {
          $scope.showpwd = !$scope.showpwd;
      };
      $scope.sendNewPwd = function() {
          console.log($scope.pwdforgetvar);
      }

  }
]);
