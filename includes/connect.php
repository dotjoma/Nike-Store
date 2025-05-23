<?php
    $host = "localhost";
    $database = "db_nike";
    $user = "root";
    $password = "";
    $dsn = "mysql:host={$host};dbname={$database};";

    try
    {
        $conn = new PDO($dsn, $user, $password);
        // if ($con) echo "Successfully connected to database.";
    }
    catch (PDOException $th)
    {
        echo $th->getMessage();
    }
?>