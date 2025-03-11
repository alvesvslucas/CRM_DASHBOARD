<?php
include '../config/database.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard TV - CRM</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
      overflow: hidden;
    }
    
    .slide {
      display: none;
      animation: fadeIn 1s;
    }
    
    .active {
      display: block;
    }
    
    .chart-container {
      width: 100%;
      max-width: 600px;
      height: 350px;
      margin: auto;
    }
    
    .alert-container {
      position: fixed;
      top: 10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80%;
      z-index: 1000;
      text-align: center;
    }
    
    .alert {
      display: none;
      font-size: 20px;
      padding: 15px;
      border-radius: 8px;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  </style>
</head>
<body>

  <!-- Container de Alertas -->
  <div class="alert-container">
    <div id="alerta" class="alert"></div>
  </div>

  <div class="container text-center mt-5">
    <h2 class="mb-4">Painel de Métricas - Exibição Automática</h2>

    <div id="slides">
      <div class="slide active">
        <h4>Participação de Vendas</h4>
        <div class="chart-container">
          <canvas id="graficoPizza"></canvas>
        </div>
      </div>
      <div class="slide">
        <h4>Progresso das Metas (%)</h4>
        <div class="chart-container">
          <canvas id="graficoProgresso"></canvas>
        </div>
      </div>
      <div class="slide">
        <h4>Tendência de Crescimento</h4>
        <div class="chart-container">
          <canvas id="graficoLinha"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    let graficos = {};

    // Carrega e exibe alertas
    function carregarAlertas() {
      $.ajax({
        url: 'get_alertas.php',
        method: 'GET',
        dataType: 'json',
        success: function(alertas) {
          if (alertas.length > 0) {
            let index = 0;
            function mostrarAlerta() {
              let alerta = alertas[index];
              let alertaDiv = $("#alerta");
              alertaDiv.removeClass("alert-success alert-warning alert-danger").hide();
              
              // Ajuste de cor do alerta
              if (alerta.tipo === "sucesso") {
                alertaDiv.addClass("alert-success");
              } else {
                alertaDiv.addClass("alert-warning");
              }

              alertaDiv.text(alerta.mensagem).fadeIn(500).delay(5000).fadeOut(500, function() {
                index = (index + 1) % alertas.length;
                mostrarAlerta();
              });
            }
            mostrarAlerta();
          }
        },
        error: function(error) {
          console.error("Erro ao carregar alertas:", error);
        }
      });
    }

    // Carrega e atualiza gráficos
    function carregarGraficos() {
      $.ajax({
        url: 'get_dados.php',
        method: 'GET',
        dataType: 'json',
        success: function(dados) {
          atualizarGrafico("graficoPizza", "Participação de Vendas", dados.participacao, 'pie');
          atualizarGrafico("graficoProgresso", "Progresso das Metas (%)", dados.progresso, 'bar');
          atualizarGrafico("graficoLinha", "Tendência de Crescimento", dados.tendencia, 'line');
        },
        error: function(error) {
          console.error("Erro ao carregar gráficos:", error);
        }
      });
    }

    function atualizarGrafico(idCanvas, titulo, data, tipo) {
      let ctx = document.getElementById(idCanvas).getContext('2d');

      // Se já existir um gráfico anterior, destruir antes de recriar
      if (graficos[idCanvas]) {
        graficos[idCanvas].destroy();
      }

      graficos[idCanvas] = new Chart(ctx, {
        type: tipo,
        data: {
          labels: data.labels,
          datasets: [{
            label: titulo,
            data: data.values,
            backgroundColor: [
              "rgba(255, 99, 132, 0.6)",
              "rgba(54, 162, 235, 0.6)",
              "rgba(255, 206, 86, 0.6)",
              "rgba(75, 192, 192, 0.6)"
            ],
            borderColor: [
              "rgba(255, 99, 132, 1)",
              "rgba(54, 162, 235, 1)",
              "rgba(255, 206, 86, 1)",
              "rgba(75, 192, 192, 1)"
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
    }

    // Rotação de slides
    function iniciarSlides() {
      let slides = document.querySelectorAll('.slide');
      let index = 0;
      function trocarSlide() {
        slides.forEach(slide => slide.classList.remove('active'));
        slides[index].classList.add('active');
        index = (index + 1) % slides.length;
      }
      setInterval(trocarSlide, 10000);
    }

    // Ao carregar a página
    $(document).ready(function() {
      carregarAlertas();
      carregarGraficos();
      iniciarSlides();

      // Atualiza alertas a cada 30s
      setInterval(carregarAlertas, 30000);

      // Atualiza gráficos a cada 60s
      setInterval(carregarGraficos, 60000);
    });
  </script>

</body>
</html>
