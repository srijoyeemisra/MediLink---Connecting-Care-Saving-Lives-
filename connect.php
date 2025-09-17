<?php
$SERVER_NAME="localhost";
$USER_NAME="root";
$PASSWORD="";
$DATABASE="hack-2-heal-db";

$conn=new PDO("mysql:host=$SERVER_NAME; dbname=$DATABASE",$USER_NAME,$PASSWORD);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
