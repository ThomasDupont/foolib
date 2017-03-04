angular.module('routeApp').controller('ProfilController', ['$scope', '$location', 'Ajax', 'Upload',
    function($scope, $location, Ajax, Upload){
        vm = this;
        vm.showUpdate = false;
        vm.fileOk = false;
        $scope.fileNameChanged = function() {
           vm.fileOk = true;
        }

        vm.genericModel = "";
        vm.updateProfil = {
            show: function () {
                vm.showUpdate = !vm.showUpdate;
            },
            update: function() {
                if(vm.fileOk) {
                    var file = document.getElementsByClassName('fileUploadPPup');
                    var nodeID;
                    for(var i=0, nodes = $scope.$parent.$parent.nodes; i<nodes.length; i++) {
                        if(nodes[i].parentNode_ID == 0) {
                            nodeId = nodes[i].node_ID;
                        }
                    }

                    Upload.upload(file[0], nodeId, true , function (promise) {
                        vm.fileOk = false;
                        $scope.$parent.$parent.pprofil = USERDIR+promise.data.result.path;
                    });
                }
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
