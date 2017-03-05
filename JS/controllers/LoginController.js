angular.module('routeApp').controller('LoginController', ['$scope', '$location', 'Ajax', 'Upload',
    function($scope, $location, Ajax, Upload){
        $scope.showpwd = false;
        $scope.pwdforgetvar = "";

        $scope.login = function () {
          Ajax.login($scope.username, $scope.password).then(
              function(promise){
                  if(promise.data.success) {
                      $scope.$parent.isDisconnectable = true;
                      $scope.$parent.userName = promise.data.name;
                      $location.path('home');
                  } else {
                      $scope.PostDataResponse = "Erreur d'authentification";
                  }
              }) ;
        }

        $scope.register = function () {
          if($scope.password === $scope.passwordConfirm) {
              Ajax.register($scope.username, $scope.email, $scope.password).then(
                  function(promise){
                      if(promise.data.success) {
                          $scope.$parent.isDisconnectable = true;
                          $scope.$parent.userName = $scope.username;
                          $scope.$parent.userEmail = $scope.email;
                          $scope.$parent.userFolder = promise.data.result.path;
                          $scope.$parent.userFolderId = promise.data.result.nodeId;
                          $location.path('home');
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
