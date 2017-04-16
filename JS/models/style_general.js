// Affichage Popup Profil
var style_general = function() {

    this.selectLang = 'HTML';
    this.l_profile = function() {
        $("#popup_filtre").css('display','block');
        $("#popup_profile").fadeIn();
    };

    this.popup_filtre = function(e) {
        $(e.target).fadeOut();
        $("#close_edit_profile").fadeOut();
        $("#l_edit_profile").fadeIn();
        $("#base_profile").fadeIn();
        $("#popup_profile").css('top','calc(50% - 162px)');
        $("#popup_profile").children('form').css('display','none');
        $("#popup_profile").css('display','none');
        $("#popup_languages").css('display','none');
    };

    // Affichage Partie Edition

    this.l_edit_profile = function(e) {
        $(e.target).fadeOut();
        $("#base_profile").css('display','none');
        $("#popup_profile").children('form').fadeIn();
        $("#close_edit_profile").fadeIn();
        $("#popup_profile").css('top','calc(50% - 246px)');
    };

    this.close_edit_profile = function(e) {
        $(e.target).fadeOut();
        $("#base_profile").fadeIn();
        $("#popup_profile").children('form').css('display','none');
        $("#l_edit_profile").fadeIn();
        $("#popup_profile").css('top','calc(50% - 162px)');
    };

    // Affichage Selection de Langages

    this.l_select_language = function() {
        $("#popup_filtre").css('display','block');
        $("#popup_languages").fadeIn();
    };

    this.popup_languages_ul_li = function(e) {
        $('#popup_languages').children('ul').children('li').css('color','#555555');
        $(e.target).css('color','#25C997');
        this.selectLang = $(e.target).text();
        $("#popup_languages").fadeOut();
        $("#popup_filtre").css('display','none');
    };

    // Affichage du code selon le langage
    this.aff_language_ul_li = function(e) {
        $("#aff_language").children("ul").children("li").css('background','#F4F4F4');
        $(e.target).css('background','#FFFFFF');
    };

    // Menu Active
    this.liste_ul_li = function(e) {
        $("#liste").children("ul").children("li").css('color','rgba(255,255,255,0.6)');
        $("#liste").children("ul").children("li").children("div").css('display','none');
        $(e.target).css('color','#FFFFFF');
        $(e.target).children("div").fadeIn();
    };
};
