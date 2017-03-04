angular.module('routeApp').factory('Upload', function($http, $location, $sce) {
    var controller = "code";
    return {
        csrfToken: "",
        test: function (vm) {
            //vm.injectNewFile = 'Salut';
            $http.post("http://localhost:8080/test").then(function(promise) {
                console.log(promise);
            });
        },

        upload: function (file, parentNodeId, profil , onSuccess) {

            var reader = new FileReader();
            reader.readAsDataURL(file.files[0]);
            var csrf = this.csrfToken;
            reader.onload = function(e) {
                 $http.post(
                   APP+"/ajax/upload/",
                   {file : reader.result, filename: file.files[0].name, pNodeId: parentNodeId, profil, csrf: csrf}
               ).then( function (promise){
                   if(promise.data.success) {
                       onSuccess(promise) ;
                   }
               });
            };
        },

        getCodes: function() {
            return $http.post(
                APP+"/"+controller+"/getcodes/",
                {csrf: this.csrfToken}
            );
        },
        createFile: function (content, title, parentNodeId, langage, onSuccess) {
            //var base64 = "data:text/txt;base64,"+btoa(content);
            var base64 = btoa(content);
            $http.post(
                APP+"/"+controller+"/createfile/",
                {file : base64, filename: title, pNodeId: parentNodeId, langage: langage, csrf: this.csrfToken}
            ).then( function(promise) {

                if(promise.data.success) {
                    onSuccess(promise) ;
                }
            });
        },
        supprCode: function(id) {
            return $http.post(APP+"/"+controller+"/supprcode/",
                {id: id, csrf: this.csrfToken}
            );
        },
        updateCode: function(el) {
            return $http.post(APP+"/"+controller+"/updatecode/",
                {element: el, csrf: this.csrfToken}
            );
        }/*,

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
        }*/
    };
});
