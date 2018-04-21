"use strict";
var dropFile = function(params, limit) {
    var dropArea = document.getElementById(params.dropArea),
        inputF = document.getElementsByClassName(params.fileInput),
        supr = document.getElementsByClassName(params.supr),
        droppedFiles = [];

    // gestionnaires
    this.init = function () {
        dropArea.addEventListener('drop', this.handleDrop, false);
        dropArea.addEventListener('dragover', this.handleDragOver, false);
        dropArea.getElementsByTagName('button')[0].addEventListener('click', this.selectFile, false);
        for(var i = 0; i<inputF.length; i++) {
            inputF[i].addEventListener('change', this.addFileFromInput, false);
            supr[i].addEventListener('click', this.delDroppedFiles, false);
        }

    };
    //nettoyage
    this.clean = function() {
        dropArea.removeEventListener('drop', this.handleDrop, false);
        dropArea.removeEventListener('dragover', this.handleDragOver, false);
        dropArea.getElementsByTagName('button')[0].removeEventListener('click', this.selectFile, false);
        for(var i = 0; i<inputF.length; i++) {
            inputF[i].removeEventListener('change', this.addFileFromInput, false);
            supr[i].removeEventListener('click', this.delDroppedFiles, false);
        }

    };

    // survol lors du déplacement
    this.handleDragOver = function (event) {
        event.stopPropagation();
        event.preventDefault();

        dropArea.className = 'hover';
    };

    // glisser déposer
    this.handleDrop = function (event) {
        event.stopPropagation();
        event.preventDefault();
        for(var i = 0; i<event.dataTransfer.files.length; i++) {
            if(droppedFiles.length > limit-1) {
                alert("Pas plus de "+limit+" fichiers");
                return false;
            } else {
                droppedFiles.push({
                    drop: true,
                    data: event.dataTransfer.files[i]
                });
                document.getElementById("info"+(droppedFiles.length-1)).innerHTML = event.dataTransfer.files[i].name;
                supr[droppedFiles.length-1].style.display = "block";
            }
        }
    };
	//A la selection de fichier
    this.selectFile = function  () {

        if(droppedFiles.length > limit-1) {
            alert("Pas plus de "+limit+" fichiers");
            return false;
        } else {
            inputF[droppedFiles.length].click();
        }
    };
	//Ajout fichier selectionner
    this.addFileFromInput = function () {
        droppedFiles.push({
            drop: false,
            data: this.files[0]
        });
        document.getElementById("info"+(droppedFiles.length-1)).innerHTML = this.files[0].name;
        supr[droppedFiles.length-1].style.display = "block";
    };
	//récupérer les résultats
    this.getResultObject = function () {
        return droppedFiles;
    };
	//supprimer un fichier
    this.delDroppedFiles = function() {
        var id = this.getAttribute('data-id');
        var tmp = [];
        for(var i = 0; i<droppedFiles.length; i++) {
            document.getElementById("info"+i).innerHTML = "";
            supr[i].style.display = "none";
            if(i != id) {
                tmp.push(droppedFiles[i]);
            }
        }
        droppedFiles = tmp;
        for(var i = 0; i<droppedFiles.length; i++) {
            supr[i].style.display = "block";
            document.getElementById("info"+i).innerHTML = droppedFiles[i].data.name;
        }
    };
};

