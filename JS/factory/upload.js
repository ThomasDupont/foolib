angular.module('routeApp').factory('Upload', function($http, $location, $sce) {
    var controller = "ajax";
    return {
        csrfToken: "",
        test: function (vm) {
            vm.injectNewFile = 'Salut';
        },
        upload: function (file, parentNodeId , onSuccess) {

            var reader = new FileReader();
            reader.readAsDataURL(file.files[0]);

            reader.onload = function(e) {
                 $http.post(
                   APP+"/"+controller+"/upload/",
                   {file : reader.result, filename: file.files[0].name, pNodeId: parentNodeId, csrf: this.csrfToken}
               ).then( function (promise){
                   if(promise.data.success) {
                       onSuccess(promise) ;
                   }
               });
            };
        },
        createFile: function (content, title, parentNodeId, langage, onSuccess) {
            var base64 = "data:text/txt;base64,"+btoa(content);
            console.log(base64);
            $http.post(
                APP+"/"+controller+"/createfile/",
                {file : base64, filename: title+".txt", pNodeId: parentNodeId, langage: langage, csrf: this.csrfToken}
            ).then( function(promise) {

                if(promise.data.success) {
                    onSuccess(promise) ;
                }
            });
        },
        deleteNode : function (nodeId) {
            return $http.post(
                APP+"/"+controller+"/deletenode/",
                {nodeId: nodeId, csrf: this.csrfToken}
            );
        },
        createFolder : function (nodeId, name) {
            return $http.post(
                APP+"/"+controller+"/createfolder/",
                {nodeId: nodeId, name: name, csrf: this.csrfToken}
            );
        }
    };
});
