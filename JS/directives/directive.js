angular.module('routeApp')
.directive("folder", function(){

    return {
          restrict: 'E',
          scope: {
            folder: '=folder',
            controller: '=controller'
          },
          createScope : false,
          templateUrl: 'views/template/folder.html',
          link: function(scope, element, attrs){
              scope.folder.isFolder = scope.folder.path.match(/\./) === null;

              scope.folder.isPreview = function(path) {
                  return path.indexOf(".txt") != -1;
              }
              scope.toggle = function(folder){
                  scope.controller.pathInUpload = folder.path;
                  scope.controller.nodeidUpload = folder.node_ID;
                  scope.controller.selected =  folder ;
                  folder.isSelected =  !folder.isSelected;
              };
              scope.preview = function(path) {
                  console.log(USERDIR+path);
                  return USERDIR+path;
              }
          }
    };
}).directive("file", function(){
    return {
        restrict: 'E',
        scope: {
          controller: '=controller'
        },
        createScope : false,
        templateUrl: 'views/template/file.html',
        link: function(scope, element, attrs){

        }
    };
}).directive("code", function(){
    return {
        restrict: 'E',
        scope: {
          controller: '=controller'
        },
        createScope : false,
        templateUrl: 'views/template/code.html',
        link: function(scope, element, attrs){

        }
    };
});
