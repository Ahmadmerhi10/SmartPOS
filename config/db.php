<?php

try{
    $host="localhost";
    $dbname="smartpos";
    $username="root";
    $password="";

    $conn=new PDO("mysql:host=$host;dbname=$dbname",$username,$password);

    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){
    echo $e->getMessage();
}

?>