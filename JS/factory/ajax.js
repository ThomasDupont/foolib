angular.module('routeApp').factory('Ajax', function($http, $location, $sce) {
    var controller = "ajax";

    return {
        csrfToken: "",
        csrf: function () {
            return $http.post(APP+"/csrf/set/" , {controller: "CSRF"});
        },
        test : function() {
            return $http.post(APP+"/"+controller+"/test/" , {test: {action : "ici"}, csrf: this.csrfToken});
        },
        contact: function (text) {
            return $http.post(APP+"/"+controller+"/sendcontact/" , {text : text, csrf: this.csrfToken});
        },
        getHome: function (text) {
            return $http.post(APP+"/"+controller+"/gethome/" , { csrf: this.csrfToken});
        },
        checkUser: function (text) {
            return $http.post(APP+"/"+controller+"/checkuser/" , { csrf: this.csrfToken});
        },
        disconnect: function () {
            return $http.post(APP+"/"+controller+"/disconnect/" , { csrf: this.csrfToken}).then(function (promise) {
              $location.path('login');
            });
        },
        login: function (log, psw) {
            return $http.post(APP+"/"+controller+"/login/" , {login : log, password: psw, csrf: this.csrfToken});
        },
        register: function (log, email, psw) {
            return $http.post(APP+"/"+controller+"/register/" , {login : log, email:email, password: psw, csrf: this.csrfToken});
        },
        updateProfil: function(pwd, name, email) {
            return $http.post(APP+"/"+controller+"/updateprofil/",
                {login: name, email: email, password: pwd, csrf: this.csrfToken}
            );
        },
        onError: function(error) {
            switch(error.status) {
                case 404:
                    alert("Vous n'êtes pas connecté, ou l'opération est introuvable");
                    break;
                case 500:
                    alert("Error serveur");
                    break;
                case 403:
                    alert("Vous n'avez pas la permission d'effectuer cette opération");
                    break;
                default:
                    alert("Erreur status "+error.status);
                    break;
            }
        },
        testError: function() {
            $http.post(APP).then(null, function(error){
                console.log(error.status);
            })
        }
    }
});
