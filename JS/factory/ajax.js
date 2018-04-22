angular.module('foolib').factory('Ajax', function($http, $location) {
    var controller = "user";

    return {
        csrfToken: "",
        csrf: function () {
            return $http.post(APP+"/csrf/set" , {controller: "CSRF"});
        },
        contact: function (text) {
            return $http.post(APP+"/"+controller+"/sendcontact" , {text : text, csrf: this.csrfToken});
        },
        checkUser: function (text) {
            return $http.post(APP+"/"+controller+"/checkuser" , {crypt: text, csrf: this.csrfToken});
        },
        disconnect: function () {
            return $http.post(APP+"/"+controller+"/disconnect" , { csrf: this.csrfToken}).then(function () {
              $location.path('login');
            });
        },
        login: function (log, psw, type) {
            return $http.post(APP+"/"+controller+"/login" , {login : log, password: psw, type: type, csrf: this.csrfToken});
        },
        register: function (log, email, psw) {
            return $http.post(APP+"/"+controller+"/register" , {login : log, email:email, password: psw, csrf: this.csrfToken});
        },
        confirmMail: function(token) {
            return $http.post(APP+"/"+controller+"/confirmemail",
                {token: token, csrf: this.csrfToken}
            );
        },
        sendNewPwd: function(newp, token) {
            return $http.post(APP+"/"+controller+"/setnewpassword",
                {token: token, newpwd: newp, csrf: this.csrfToken}
            );
        },
        updateProfil: function(pwdOld, pwdNew, name, email) {
            return $http.post(APP+"/"+controller+"/updateprofil",
                {login: name, email: email, passwordOld: pwdOld, passwordNew: pwdNew, csrf: this.csrfToken}
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
        sendemail : function(params, type) {
            return $http.post(APP+"/"+controller+"/forgotpwdsendemail" , {params: params, csrf: this.csrfToken});
        },
    }
});
