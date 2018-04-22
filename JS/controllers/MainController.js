angular.module('foolib').controller('MainController', ['$scope', 'Ajax', 'Upload', 'mainFactory',
    function($scope, Ajax, Upload, mainFactory){
        this.mainFactory = mainFactory;
        this.mainFactory.viewClass = 'container';
    }
]);
