angular.module('routeApp').controller('LoginController', ['$scope', '$location', 'Ajax',
    function($scope, $location, Ajax){
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
              Ajax.register($scope.username, $scope.password).then(
                  function(promise){
                      if(promise.data.success) {
                          $scope.$parent.isDisconnectable = true;
                          $scope.$parent.userName = promise.data.name;
                          $location.path('home');
                      } else {
                          scope.PostDataResponse = "Erreur d'authentification";
                      }
                  });
          } else {
              $scope.PostDataResponse = "Les mots de passe ne correspondent pas";
          }

        }
    }
]);
