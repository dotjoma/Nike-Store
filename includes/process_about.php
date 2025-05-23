<?php
    require_once("connect.php");

    if (isset($_POST["txtTitle"])) {
        $id = isset($_POST["txtId"]) ? $_POST["txtId"] : null;
        $title = htmlspecialchars(trim($_POST["txtTitle"]));
        $content = htmlspecialchars(trim($_POST["txtContent"]));
    
        $title = filter_var($title, FILTER_SANITIZE_URL);
        $content = filter_var($content, FILTER_SANITIZE_URL);
    
        try {
            if (!empty($id)) {
                $sql = "UPDATE aboutus SET atitle=?, acontent=? WHERE aboutid=?";
                $data = array($title,$content,$id);
                $stmt = $con->prepare($sql);
                $stmt->execute($data);
            } else {
                $sql = "INSERT INTO aboutus (atitle,acontent) VALUES(?,?)";
                $data = array($title,$content);
                $stmt = $con->prepare($sql);
                $stmt->execute($data);
            }
            header("location: ../aboutus.php");
        } catch (PDOException $th) {
            echo $th->getMessage();
        }
    }

    if(isset($_GET['delid']))
    {
        $delSql = "DELETE FROM aboutus WHERE md5(aboutid)=?";
        $data=array($_GET['delid']);

        try {
            $stmtDel = $con->prepare($delSql);
            $stmtDel->execute($data);
            header("location: ../aboutus.php");
        } catch (PDOException $th) {
            echo $th->getMessage();
        }
    }

    if (isset($_GET["viewid"]))
    {   
        try {
            $id = $_GET["viewid"];
            $sqlLoad = "SELECT * FROM aboutus WHERE md5(aboutid)=?";
            $stmtLoad = $con->prepare($sqlLoad);
            $dataLoad = array($id);
            $stmtLoad->execute($dataLoad);
            $rowLoad = $stmtLoad->fetch();
            $strId = $rowLoad[0];
            $strTitle = $rowLoad[1];
            $strContent = $rowLoad[2];
            echo $strTitle;
            echo $strContent;
        } catch (PDOException $th) {
            echo $th->getMessage();
        }
    }
?>