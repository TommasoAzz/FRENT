<?php
//require_once "./Database.class.php";
//require_once "./Frent.class.php";
//require_once ($_SERVER["DOCUMENT_ROOT"]) . "/php/CredenzialiDB.class.php";
//
//$nome = $_POST["user"];
//$password = $_POST["password"];
//try {
//    $db = new Database(CredenzialiDB::DB_ADDRESS, CredenzialiDB::DB_USER,
//        CredenzialiDB::DB_PASSWORD, CredenzialiDB::DB_NAME);
//
//    $frent = new Frent($db);
//
//    $user = $frent->login($nome, $password);
//
//    session_start();
//    $_SESSION["user"] = $user;
//    header("Location: ../pagine_php/index.php");
//
//} catch (Eccezione $e) {
//    header("Location: ../pagine_php/login.php?error_code=1");
//}
//
//
//
