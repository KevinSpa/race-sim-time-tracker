<?php
// Redirect users to the home page if they access this file directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Location: ../');
    exit();
}
require_once("inc/auth.php");
require("conn.php");
require_once("functions.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assetto Corsa</title>

    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <link rel="stylesheet" href="assets/Formula1-Regular.ttf">
    <link rel="icon" href="assets/img/tab_icon.png" width='100%'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>

<div class="mobileNav">
    <div class="row">
        <div class="col-6 p-3 d-flex align-items-center justify-content-center">
            <img src="assets/img/logo_white.png" class="w-50">
        </div>
        <div class="col-6 d-flex align-items-center justify-content-end" id="toggleMenu">
            <button class="btn btn-outline-light" id="menuToggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                     class="bi bi-border-width" viewBox="0 0 16 16">
                    <path
                        d="M0 3.5A.5.5 0 0 1 .5 3h15a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H.5a.5.5 0 0 1-.5-.5v-2zm0 5A.5.5 0 0 1 .5 8h15a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H.5a.5.5 0 0 1-.5-.5v-1zm0 4a.5.5 0 0 1 .5-.5h15a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-fixed-left flex-column align-items-start vh-100 nav-position" id="mainNav">
    <a href="../" class="p-0 m-0 logolink">
        <div class="logo">
            <img src="assets/img/logo_white.png" class="w-50">
        </div>
    </a>

    <ul class="navbar-nav d-flex flex-column w-100">
        <li class="nav-item">
            <a class="nav-link" href="./">🏠 Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="brands">🏎️ Cars</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="tracks">🏁 Tracks</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="times">⏱️ Lap Times</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="topspeed">🔝 Top speed</a>
        </li>

        <p></p>
        <?php
        if ((int)$_SESSION["user_id"] == 1) {
            ?>
            <li class="nav-item">
                <a class="nav-link add-lap-time" href="newtime">✅ Add Lap Time</a>
            </li>
            <?php
        }
        ?>

        <li class="nav-item logoutBtn">
            <a class="nav-link text-danger" href="logout">Logout</a>
        </li>
    </ul>
</nav>

<div class="container pt-5 main-content">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

