<!DOCTYPE html>
<html lang="en">
<head>
  <title>El colegio tiene talento</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'menu.php';?>
<div class="container text-center">    
  <h3 class="Categorias">Festival El Colegio Tiene Talentos</h3><br>
  <div class="row">
    <img src="ImagenPrincipal.jpeg" alt="Objetivos" class="imgcentral">
    <div class="col-sm-4">
      <img src="BailesGrupales.jpeg" alt="Baile grupal" class="img-responsive">
      <p class="Categorias">Bailes Grupales</p>
    </div>
    <div class="col-sm-4"> 
      <img src="talentos individuales.jpeg" alt="Talento individual" class="img-responsive">
      <p class="Categorias">Talentos Individuales</p>    
    </div>
    <div class="col-sm-4"> 
      <img src="TalentoDeportivo.jpeg" alt="Talento deportivo" class="img-responsive">
      <p class="Categorias">Talentos Deportivos</p>    
    </div>
    <div class="col-sm-4">
      <div class="well">
       <h4 class="Color-text">talento individual</h4>
       <p class="Color-text">Canto, Bailes, Instrumento</p>
      </div>
      <div class="well" >
       <h4 class="Color-text">Bailes Grupales</h4>
       <ul class="Color-text">
          <li>Categoria Prejuvenil</li>
          <li>Categoria Juvenil</li>
       </ul>
      </div>
      <div class="well">
       <h4 class="Color-text">talento Deportivos</h4>
       <p class="Color-text">muestranos que deportes pradticas</p>
      </div>
    </div>
  </div>
</div><br>
<?php include 'PiePagina.php'; ?>
</body>
</html>