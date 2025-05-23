<?php
    require_once("connect.php");
    require_once("functions.php");

    if (isset($_POST["txtTitle"])) {
        $id=$_POST["txtId"];
        $title=cleanText($_POST["txtTitle"]);
        $author=cleanText($_POST["txtAuthor"]);
        $datePosted=$_POST["dtDate"];
        $story=cleanText($_POST["txtStory"]);

        if($id==0){
            $sql="INSERT INTO news (title, author, date_posted, story) VALUES (?, ?, ?, ?)";
            $data=array($title, $author, $datePosted, $story);

        }else{
            $sql="UPDATE news SET title=?, author=?, date_posted=?, story=? WHERE md5(id)=?";
            $data=array($title, $author, $datePosted, $story, $id);
        }

        $stmt = $con->prepare($sql);
        $stmt->execute($data);

        if($id==0){
            $newName=$con->lastInsertId();
        }else{
            $sqlPic="SELECT id FROM news WHERE id=?";
            $dataPic=array($id);
            $stmtPic=$con->prepare($sqlPic);
            $stmtPic->execute($dataPic);
            $rowPic=$stmtPic->fetch();
            $newName=$rowPic[0];
        }

        $fileName=$_FILES["picture"];
        if(!(empty($fileName["name"]))){
            $upload_directory="../uploads/news/";
            uploadFile($fileName,$newName,$upload_directory);

            $sqlUpdate="UPDATE news SET picture=? WHERE id=?";
            $extName=end(explode(".",$fileName['name']));
            $filename="{$newName}.{$extName}";
            $dataUpdate=array($filename,$newName);
            $stmpUpdate=$con->prepare($sqlUpdate);
            $stmpUpdate->execute($dataUpdate);
        }

        header(header: "location:../news.php");
    }

    if (isset($_GET['delid'])) {
        $delSql = "DELETE FROM news WHERE md5(id)=?";
        $data = [$_GET['delid']];
        try {
            $stmtDel = $con->prepare($delSql);
            $stmtDel->execute($data);
            header("location:../news.php");
        } catch (PDOException $th) {
            echo $th->getMessage();
        }
    }
?>
