angular.module('foolib').controller('TermsController', ['$scope', '$location', 'Ajax', 'Upload', 'mainFactory',
    function($scope, $location, Ajax, Upload, mainFactory){
        var vm = this;
        vm.mainFactory = mainFactory;
        vm.mainFactory.viewClass = "terms";
    }
]);
