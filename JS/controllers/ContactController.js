angular.module('routeApp').controller('ContactController', ['$scope', '$routeParams', '$http', 'Ajax',
    function($scope, $routeParams, Ajax){

        $scope.message = "Bienvenue sur la page de contact";
        $scope.msg = $routeParams.msg || "Bonne chance pour cette nouvelle appli !";
        $scope.PostDataResponse = "ta mere";

        $scope.contact = function () {
          Ajax.contact($scope.msg).then(function(promise){
                    console.log("contact");
                    $scope.PostDataResponse =promise.data ;
              }) ;
        }
    }
]);
