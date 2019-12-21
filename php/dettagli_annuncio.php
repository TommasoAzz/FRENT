<?php
require_once "./class_CredenzialiDB.php";
require_once "./CheckMethods.php";
/*
todo quando l'annuncio non approvato è visualizzato dal proprietario, il pulsante prenota deve sparire, e deve
    essere visualizza il pulsante modifica, reindirizzandolo verso la pagina della modifica dell'annunciom
    e quindi sparisce anche i due form di data.

*/

require_once "./class_Frent.php";
require_once "./class_CredenzialiDB.php";
try {
    /*
     * quando annuncio non è stato approvato, fare dei controlli che non può essere visualizzato,
     * il controllo avviene attraverso il controllo dell'auth presente in session, se è admin allora
     * può visualizzare, se è utente o null allora possono visualizzare solo gli annunci nello stato di approvazione= 1
     */
    session_start();
    $manager = new Frent(new Database(CredenzialiDB::DB_ADDRESS, CredenzialiDB::DB_USER,
        CredenzialiDB::DB_PASSWORD, CredenzialiDB::DB_NAME));
    
    if (!isset($_GET["id"])) {
        header("Location: ./404.php");
    }
    
    $id = intval($_GET["id"]);
    $_SESSION["id"] = $id;
    $annuncio = $manager->getAnnuncio($id);
    // se non sono ne admin ne user e annuncio non è stato approvato, non posso vederlo.
    // se sono user ma non sono host e annuncio non è stato approvato, non posso veder.
    if ((!isset($_SESSION["admin"]) and !isset($_SESSION["user"]) and $annuncio->getStatoApprovazione() != 1) or
        (isset($_SESSION["user"]) and $_SESSION["user"]->getIdUtente() != $annuncio->getIdHost()
            and $annuncio->getStatoApprovazione() != 1)) {
        header("Location: ./404.php");
    }
    
    $_SESSION["annuncio"] = $annuncio;
    $prezzoAnnuncio = $annuncio->getPrezzoNotte();
    $ospitiMassimo = $annuncio->getMaxOspiti();
    $foto = $manager->getFotoAnnuncio($id);
    
    $pagina = file_get_contents("./components/dettagli_annuncio.html");
    $pagina = str_replace("<DESCRIZIONE/>", $annuncio->getDescrizione(), $pagina);
    
    // impostazione della pagina in base al tipo di utenza
    if (isset($_SESSION["user"])) {
        $pagina = str_replace("<HEADER/>", file_get_contents("./components/header_logged.html"), $pagina);
        if ($annuncio->getIdHost() == $_SESSION["user"]->getIdUtente()) {
            $pagina = str_replace("<FLAG/>", file_get_contents("./components/dettaglio_annuncio_host.html"), $pagina);
        } else {
            $pagina = str_replace("<FLAG/>", file_get_contents("./components/dettaglio_annuncio_visitatore.html"), $pagina);
        }
    } else if (isset($_SESSION["admin"])) {
        $pagina = str_replace("<HEADER/>", file_get_contents("./components/header_admin_logged.html"), $pagina);
        $pagina = str_replace("<FLAG/>", file_get_contents("./components/dettaglio_annuncio_admin.html"), $pagina);
        $params_si = "?idAnnuncio=" . $annuncio->getIdAnnuncio() . "&approvato=1";
        $params_no = "?idAnnuncio=" . $annuncio->getIdAnnuncio() . "&approvato=0";
        $pagina = str_replace("<PARAMS_SI/>", $params_si, $pagina);
        $pagina = str_replace("<PARAMS_NO/>", $params_no, $pagina);
    } else {
        $pagina = str_replace("<HEADER/>", file_get_contents("./components/header_no_logged.html"), $pagina);
        $pagina = str_replace("<FLAG/>", file_get_contents("./components/dettaglio_annuncio_visitatore.html"), $pagina);
       require_once "./components/setMinMaxDates.php";
    }
    
    if (isset($_GET["dataInizio"])) {
        $dataInizio = $_GET["dataInizio"];
        $_SESSION["dataInizio"] = $dataInizio;
        $pagina = str_replace("<VALUEINIZIO/>", $dataInizio, $pagina);
    } else {
        $pagina = str_replace("<VALUEINIZIO/>", "", $pagina);
    }
    
    if (isset($_GET["dataFine"])) {
        $dataFine = $_GET["dataFine"];
        $_SESSION["dataFine"] = $dataFine;
        $pagina = str_replace("<VALUEFINE/>", $dataFine, $pagina);
    } else {
        $pagina = str_replace("<VALUEFINE/>", "", $pagina);
    }
    
    if (isset($_GET["numOspiti"])) {
        $numOspiti = $_GET["numOspiti"];
        $_SESSION["numOspiti"] = $numOspiti;
        $pagina = str_replace("<VALUENUMERO/>", intval($_GET["numOspiti"]), $pagina);
    } else {
        $pagina = str_replace("<VALUENUMERO/>", 1, $pagina);
    }
    
    if (isset($_SESSION["dati_errati"]) and $_SESSION["dati_errati"] == "true") {
        $pagina = str_replace("<MSG/>", "<p>Dati inseriti per la prenotazione non sono validi</p>", $pagina);
    }
    
    
    $pagina = str_replace("<TITOLO_ANNUNCIO/>", $annuncio->getTitolo(), $pagina);
    $str_commenti = "";
    try {
        $commenti = $manager->getCommentiAnnuncio($id);
        $mediaCommenti = 0;
        if (count($commenti)!= 0){
            $str_commenti .= "<ul>";
            $totale = 0;
            foreach ($commenti as $commento) {
                $totale += intval($commento->getValutazione());
                $immagine_profilo = "../immagini/me.jpg";
                $testo_commento = $commento->getCommento();
                $votazione = $commento->getValutazione();
                $user_name = "xwen";
                $data_commento = $commento->getDataPubblicazione();
                $titolo_commento = $commento->getTitolo();
                $str_commenti .= "
                        <li>
                            <div class=\"intestazione_commento\">
                                <img src=\"$immagine_profilo\" alt=\"\"/>
                                <div>
                                    <p class=\"username_commento\">$user_name</p>
                                    <p class=\"data_commento\">$data_commento</p>
                                </div>
                                <p class=\"totale_commenti_utente\">Numero Commenti Totali</p>
                            </div>
                            <div class=\"corpo_commento\">
                                <h1>$titolo_commento</h1>
                                <p>Votazione: $votazione</p>
                                <p>$testo_commento</p>
                            </div>
                        </li>";
            }
            $str_commenti .= "</ul>";
            $pagina = str_replace("<Commenti/>", $str_commenti, $pagina);
        }else{
            $pagina = str_replace("<Commenti/>", "<h2>Non ci sono commenti!</h2>", $pagina);
    
        }
    
        $pagina = str_replace("<Commenti/>", $str_commenti, $pagina);
        $pagina = str_replace("<VALUTAZIONE/>", $mediaCommenti, $pagina);
        $pagina = str_replace("<NUMEROCOMMENTI/>", count($commenti), $pagina);
        $pagina = str_replace("<PREZZO/>", $prezzoAnnuncio, $pagina);
        $pagina = str_replace("<OSPITIMASSIMO/>", $mediaCommenti, $pagina);
        $pagina = str_replace("<Valutazione/>", $mediaCommenti, $pagina);
        
    } catch (Eccezione $e) {
        $pagina = str_replace("<Commenti/>", "<p>Ancora non ci sono commenti!</p>", $pagina);
    }
    
    $img = $annuncio->getImgAnteprima();
    $photos = $manager->getFotoAnnuncio($annuncio->getIdAnnuncio());
    
    $content = "<div class=\"shower_immagine_anteprima\">";
    if (count($photos) != 0) {
        $content .= "
            <button id=\"immagine_precedente\" class=\"pulsanti_navigazione_immagini\" onclick=\"\">&lt;</button>
            <img id=\"immagine_anteprima\" class=\"immagine_anteprima\" src=\"$img\" alt=\"Descrizione immagine\"/>
            <button id=\"immagine_successiva\" class=\"pulsanti_navigazione_immagini\" onclick=\"\">&gt;</button></div><div class=\"image_picker\">";
        foreach ($photos as $foto) {
            $path = $foto->getFilePath();
            $content .= "<img class=\"immagine\" alt=\"Descrizione immagine\" src=\"$path\"/>";
            
        }
    } else {
        $content .= "<img id=\"immagine_anteprima\" class=\"immagine_anteprima\" src=\"$img\" alt=\"Descrizione immagine\"/>";
    }
    $content .= "</div>";
    
    
    $pagina = str_replace("<IMMAGINE/>", $content, $pagina);
    echo $pagina;
    
} catch (Eccezione $ex) {
    echo "eccezione " . $ex->getMessage();
}
