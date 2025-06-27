<?php
$dbhost="localhost";
$dbuser="root";
$dbpass="";
$dbname="login_db";

if (!$con = new mysqli($dbhost, $dbuser, $dbpass, $dbname)) {
    die("failed to connect!");
}