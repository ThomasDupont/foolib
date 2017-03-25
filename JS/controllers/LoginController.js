angular.module('routeApp').controller('LoginController', ['$scope', '$routeParams', '$location', 'Ajax', 'Upload',
    function($scope, $routeParams, $location, Ajax, Upload){
        $scope.showpwd = false;
        $scope.pwdforgetvar = "";

        $scope.login = function () {
          Ajax.login($scope.username, $scope.password).then(
              function(promise){
                  if(promise.data.success) {
                      $scope.$parent.isDisconnectable = true;
                      $scope.$parent.userName = promise.data.result.name;
                      $scope.$parent.pprofil = USERDIR+promise.data.result.pp;
                      location.replace('/');
                  } else {
                      $scope.PostDataResponse = promise.data.message;
                  }
              }) ;
        }

        $scope.register = function () {
          if($scope.password === $scope.passwordConfirm) {
              document.getElementById('loader').style.display = 'block';
              Ajax.register($scope.username, $scope.email, $scope.password).then(
                  function(promise){
                      if(promise.data.success) {
                          $scope.$parent.isDisconnectable = true;
                          $scope.$parent.userName = $scope.username;
                          $scope.$parent.userEmail = $scope.email;
                          $scope.$parent.userFolder = promise.data.result.path;
                          $scope.$parent.userFolderId = promise.data.result.nodeId;

                          Ajax.sendemail({email: $scope.email, login: $scope.username}, 1).then(function (promise) {
                              document.getElementById('loader').style.display = 'none';
                               $location.path('home');
                          });

                      } else {
                          $scope.PostDataResponse = "Erreur à la création du compte";
                      }
                  });
          } else {
              $scope.PostDataResponse = "Les mots de passe ne correspondent pas";
          }

      };
      $scope.pwdForgot = function () {
          $scope.showpwd = !$scope.showpwd;
      };
      $scope.sendNewPwd = function() {
          console.log($scope.pwdforgetvar);
      }

  }
]);
