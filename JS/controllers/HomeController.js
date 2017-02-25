angular.module('routeApp').controller('HomeController', ['$scope', '$http', '$location', 'Ajax', 'Upload',
    function($scope, $http, $location, Ajax, Upload){

        var vm = this;

        vm.tree = [];
        vm.dataSet = {};
        vm.pathInUpload = "";
        vm.nodeidUpload = 1;
        vm.fileSelected = false;
        vm.selected =  {};
        vm.selected.childrens = [];
        vm.addCodeBool = false;
        vm.dataLoading = false;
        //vm.dataLoading = true;
        Ajax.getHome().then(
              function(promise){
                  if(promise.data.success) {
                      vm.listTree = promise.data.result;
                      vm.organizeNode(vm.listTree);

                          /*
                          EQUIVALENT EN CODE
                      //parcour
                      for (var i = 0; i < vm.listTree.length; i++) {
                        //scan de tout les files pour choper les enfant de l'element en cours
                        for (var j = 0; j < vm.listTree.length; j++) {
                            //si parent ==  id de l'element en cours
                            if(vm.listTree[j].parentNode_ID ==  vm.listTree[i].node_ID){
                                //si childrens n existe pas je le créer
                                if(!vm.listTree[i].childrens){ vm.listTree[i].childrens = []}
                                //on ajoute le mome
                                vm.listTree[i].childrens.push(vm.listTree[j]) ;
                            }
                        }
                        // si l 'element en cours n'a pas de parents , on l'ajoute dans le tableau final
                        if( vm.listTree[i].parentNode_ID == 0){
                            vm.tree.push( vm.listTree[i]) ;
                        }
                      }
                      */
                      //et on a gagné
                  } else {
                      $location.path('login');
                      return false;
                  }

          }) ;

          vm.getDetail = function (path) {
              //directive file
              vm.fileSelected = true;
              vm.filePath = path;
          };

          vm.test = function () {

              console.log(vm.selected.childrens);

          };
          vm.addCode = function () {

              vm.addCodeBool = !vm.addCodeBool;

          };
          vm.addNode = function (promise, name, isFolder) {
              vm.dataLoading = false;
              if(promise.data.success) {
                  //vm.tree
                  var el = {
                      isFolder        : isFolder ,
                      node_ID         : promise.data.result.nodeId ,
                      record_name     : name ,
                      path            : promise.data.result.path ,
                      parentNode_ID   : vm.nodeidUpload ,
                      lastModif       : new Date(),
                      childrens       : []
                  }
                  vm.selected.childrens.push(el);
                  vm.listTree.push(el);
              } else {
                  alert("Une erreur est survenue");
                  return false;
              }
          };
          vm.upload = function () {
              var files = document.getElementsByClassName('fileUpload');
              for(var i = 0; i < files.length; i++) {
                  var file = files[i];
                  Upload.upload(file, vm.nodeidUpload , function (promise) {
                      vm.addNode(promise, file.files[0].name, false);
                  });
              }
          };
          vm.createFile = function() {
              vm.dataLoading = true;
              Upload.createFile(vm.codeContent, vm.codeTitle, vm.nodeidUpload, vm.codeLangage, function (promise) {
                  vm.addNode(promise, vm.codeTitle+".txt", false);
              });
          };
          vm.organizeNode = function (tab) {
              Enumerable.From(tab).ForEach(function(t){
                  t.childrens =  Enumerable.From(tab).Where("$.parentNode_ID == " + t.node_ID).ToArray() ;
              });
              vm.tree =  Enumerable.From(tab).Where("$.parentNode_ID == 0").ToArray() ;
          };
          vm.deleteNode = function (nodeId) {
              if(confirm("Vous allez supprimer cet élément, étes-vous sur?")) {
                  var tempTab = [];
                  for(var i = 0; i < vm.listTree.length; i++) {
                      if(vm.listTree[i].node_ID != nodeId) {
                          tempTab.push(vm.listTree[i]);
                      } else if (vm.listTree[i].isFolder && vm.listTree[i].node_ID == nodeId) {
                          var subObject = vm.listTree.find(function(element) {
                              return element.node_ID == vm.listTree[i].parentNode_ID;
                          });
                          vm.pathInUpload = subObject.path;
                          vm.nodeidUpload = vm.listTree[i].parentNode_ID;
                      }
                  }
                  Upload.deleteNode(nodeId).then(function (promise) {
                      if(promise.data.success) {
                          vm.organizeNode(tempTab);
                          vm.listTree = tempTab;
                      } else {
                          alert("Une erreur est survenue");
                      }
                  });
              }
          };
          vm.createFolder = function() {
              Upload.createFolder(vm.nodeidUpload, vm.folderName).then(function (promise) { vm.addNode(promise, vm.folderName, true)});
          };
      }
]);
