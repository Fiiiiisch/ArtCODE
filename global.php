<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <script src="script.js"></script>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "global_desc.php";
            break;
            case 'oldest':
            include "global_asc.php";
            break;
          }
        }
        else {
          include "global_desc.php";
        }
        
        ?>
    <style>
      @media (min-width: 768px) {
        .width-btn {
          width: 200px;
        }
      }
      
      @media (max-width: 767px) {
        .width-btn {
          width: 100px;
        } 
      } 
    </style>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>