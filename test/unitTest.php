<?php

/**
* @test CONNECT, CREATE, INSERT, SELECT, DROP
*/
if(isset($_POST['DATABASE'])) {
    $mysqli = new mysqli("127.0.0.1", "root", "root", "angular", 8889);
    /* Vérifie la connexion */
    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", mysqli_connect_error());
        //exit();
    }

    $mysqli->query("CREATE TABLE `myCity` (
                    `Name` text NOT NULL,
                    `CountryCode` text NOT NULL,
                    `District` text NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    /* Préparation de la commande d'insertion */
    $query = "INSERT INTO myCity (Name, CountryCode, District) VALUES (?,?,?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $val1, $val2, $val3);

    $val1 = 'Stuttgart';
    $val2 = 'DEU';
    $val3 = 'Baden-Wuerttemberg';

    /* Exécute la requête */
    $stmt->execute();

    $val1 = 'Bordeaux';
    $val2 = 'FRA';
    $val3 = 'Aquitaine';

    /* Exécute la requête */
    $stmt->execute();

    /* Ferme la requête */
    $stmt->close();

    print("fetch_row\n");
    /* Récupère toutes les lignes de la table myCity */
    $query = "SELECT Name, CountryCode, District FROM myCity";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_row()) {
            printf("%s (%s,%s)\n", $row[0], $row[1], $row[2]);
        }
        /* Libère le résultat */
        $result->close();
    }

    print("\nfetch_object\n");
    /* Récupère une ligne de la table myCity */
    $query = "SELECT Name, CountryCode, District FROM myCity WHERE Name = ? ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $val1);
    $stmt->execute();
    if ($result = $stmt->get_result()) {
        $object = $result->fetch_object();
        printf("%s (%s,%s)\n", $object->Name, $object->CountryCode, $object->District);

        /* Libère le résultat */
        $result->close();
    }

    $query = "SELECT Name, CountryCode, District FROM myCity WHERE Name LIKE ? ";
    $stmt = $mysqli->prepare($query);
    $par = "";
    $par &= "%".$val1."%";
    $stmt->bind_param("s", $par);
    $stmt->execute();
    if ($result = $stmt->get_result()) {
        $object = $result->fetch_object();
        printf("%s (%s,%s)\n", $object->Name, $object->CountryCode, $object->District);

        /* Libère le résultat */
        $result->close();
    }

    /* Efface la table */
    if(!$mysqli->query("DROP TABLE myCity")) {
        print("Erreur drop table ".$mysqli->error);
    }
    /* Ferme la connexion */
    $mysqli->close();
}

if(isset($_POST['ZIPARCHIVE'])) {
    $zip = new ZipArchive();
    file_put_contents('test.txt', "salut les zouzous");
    $zip->open('test.zip', ZIPARCHIVE::CREATE);
    var_export($zip->addFile('test2.txt'));
    echo '/';
    var_export($zip->addFile('test.txt', 'testArchive.txt'));
    $zip->close();
    unlink('test.txt');
    unlink('test.zip');
}
