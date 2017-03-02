angular.module('routeApp').controller('ProfilController', ['$scope', '$location', 'Ajax',
    function($scope, $location, Ajax){
        vm = this;
        vm.showUpdate = false;
        console.log($scope.$parent.nbSnippets);
        vm.updateProfil = {
            show: function () {
                vm.showUpdate = !vm.showUpdate;
            },
            update: function() {

                Ajax.updateProfil(
                    this.modifPassword1,
                    $scope.$parent.userName,
                    $scope.$parent.userEmail
                ).then(function (promise) {
                    if(promise.data.success) {
                        vm.showUpdate = !vm.showUpdate;
                    } else {
                        alert(promise.data.message);
                    }
                });

            },
            modifPassword1 : "aaaa",
            modifPassword2 : "bbbb"
        }
    }
]);
