<?php
require_once("connection.php");
require_once("functions.php");

$user_data = check_login($con); // returnează și is_admin
?>

<style>
  .navbar {
    background-color: #5c6bc0;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    color: white;
    flex-wrap: wrap;
  }

  .navbar .logo {
    font-size: 20px;
    font-weight: bold;
    margin-right: 30px;
  }

  .navbar .logo a,
  .navbar a {
    color: white;
    text-decoration: none;
    margin-right: 20px;
  }

  .navbar a:hover {
    text-decoration: underline;
  }

  .navbar .logout {
    margin-left: auto;
    background-color: #e53935;
    padding: 6px 12px;
    border-radius: 4px;
  }

  .dropdown {
    position: relative;
    display: inline-block;
  }

  .dropdown-content {
    display: none;
    position: absolute;
    background-color: #5c6bc0;
    min-width: 160px;
    z-index: 1;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 4px;
  }

  .dropdown-content a {
    color: white;
    padding: 10px 12px;
    text-decoration: none;
    display: block;
  }

  .dropdown-content a:hover {
    background-color: #3f51b5;
  }

  .dropdown:hover .dropdown-content {
    display: block;
  }
</style>

<div class="navbar">
  <div class="logo"><a href="index.php">SPS Vet</a></div>
  <a href="lista_pacienti.php">Pacienți</a>
  <a href="programari.php">Programări</a>
  <a href="proprietari.php">Proprietari</a>
  <a href="medici.php">Medici</a>

  <?php if (isset($user_data['is_admin']) && $user_data['is_admin'] == 1): ?>
    <div class="dropdown">
      <a href="#">Rapoarte</a>
      <div class="dropdown-content">
        <a href="raport_programari.php">Raport programări</a>
        <a href="raport_incasari.php">Raport încasări</a>
        <a href="raport_pacienti.php">Raport pacienți</a>
      </div>
    </div>
    <a href="admin.php">Utilizatori</a>
  <?php endif; ?>

  
  <a href="logout.php" class="logout">Logout</a>
</div>
