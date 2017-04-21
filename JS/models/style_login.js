// Affichage Connexion

var styleLogin = function() {

    this.l_login = function (e) {
        $(e.target).css('display','none');
        $("#l_register").css('display','block');
        $("#login").fadeIn();
        $("#register").css('display','none');
        $("#return_register").css('display','none');
        $("#forgot_password").css('display','none');
        $("section").css('top','calc(50% - 228px)');
    };
    // Affichage Inscription
    this.l_register = function (e) {
        $(e.target).css('display','none');
        $("#l_login").css('display','block');
        $("#register").fadeIn();
        $("#login").css('display','none');
        $("#return_register").css('display','none');
        $("#forgot_password").css('display','none');
        $("section").css('top','calc(50% - 228px)');
    };
    // Mot de passe oublié
    this.l_forgot_password = function() {
        $("section").css('top','calc(50% - 165px)');
        $("#login").css('display','none');
        $("#register").css('display','none');
        $("#return_register").fadeIn();
        $("#forgot_password").fadeIn();
    };
    this.return_register = function() {
        $("section").css('top','calc(50% - 228px)');
        $("#login").css('display','none');
        $("#return_register").css('display','none');
        $("#forgot_password").css('display','none');
        $("#login").fadeIn();
    }
}
