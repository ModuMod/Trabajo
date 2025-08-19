<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inversiones Financieras | Horizonte Capital</title>
  <meta name="description" content="Aprendé a invertir en acciones, bonos, ETFs y fondos. Compará riesgos, plazos y rendimientos con una interfaz clara y moderna.">
  <link rel="stylesheet" href="Estilos.css" />
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="#">
        <span class="brand-mark" aria-hidden="true">$</span>
        <span class="brand-text">Modu<span>Money</span></span>
      </a>

      <nav class="nav" aria-label="Principal">
        <a href="#como-funciona">Cómo funciona</a>
        <a href="#productos">Productos</a>
        <a href="#comparador">Comparador</a>
      </nav>
    </div>
  </header>

  <main>
    <!-- Hero -->
    <section class="hero">
      <div class="container hero-grid">
        <div class="hero-copy">
          <h1>Invertí con claridad y propósito</h1>
          <p>Descubrí opciones de inversión alineadas a tu perfil de riesgo: <strong>acciones</strong>, <strong>bonos</strong>, <strong>ETFs</strong> y <strong>fondos</strong>. Herramientas simples, información transparente.</p>
          <div class="cta-row">
            <a class="btn" href="#productos">Explorar productos</a>
            <a class="btn btn-ghost" href="#comparador">Comparar riesgos</a>
          </div>
        </div>
        <div class="hero-card" role="img" aria-label="Tarjeta con ejemplo de cartera diversificada">
          <div class="kpi">
            <span class="kpi-label">Cartera demo</span>
            <span class="kpi-value">+12.4% YTD</span>
          </div>
          <div class="bars" aria-hidden="true">
            <div style="--v:65%" title="ETFs 65%">ETFs</div>
            <div style="--v:20%" title="Bonos 20%">Bonos</div>
            <div style="--v:10%" title="Acciones 10%">Acciones</div>
            <div style="--v:5%"  title="Efectivo 5%">Efectivo</div>
          </div>
          <p class="note">Ejemplo educativo. No constituye recomendación.</p>
        </div>
      </div>
    </section>

    <!-- Cómo funciona -->
    <section id="como-funciona" class="section container">
      <header class="section-header">
        <h2>¿Cómo funciona?</h2>
        <p>Cuatro pasos simples para pasar del ahorro a la inversión.</p>
      </header>
      <div class="grid-4">
        <article class="card">
          <div class="icon" aria-hidden="true">🧭</div>
          <h3>Definí objetivos</h3>
          <p>Plazo, monto y tolerancia al riesgo. Es la base del plan.</p>
        </article>
        <article class="card">
          <div class="icon" aria-hidden="true">🧪</div>
          <h3>Perfil de riesgo</h3>
          <p>Conocé tu perfil: conservador, moderado o agresivo.</p>
        </article>
        <article class="card">
          <div class="icon" aria-hidden="true">🧺</div>
          <h3>Armá tu cartera</h3>
          <p>Diversificá entre <abbr title="Exchange Traded Funds">ETFs</abbr>, bonos, acciones y fondos.</p>
        </article>
        <article class="card">
          <div class="icon" aria-hidden="true">📈</div>
          <h3>Monitoreo</h3>
          <p>Rebalanceá periódicamente según mercado y objetivos.</p>
        </article>
      </div>
    </section>

    <!-- Productos -->
    <section id="productos" class="section section-alt">
      <div class="container">
        <header class="section-header">
          <h2>Productos de inversión</h2>
          <p>Elegí instrumentos que se adapten a tu horizonte temporal.</p>
        </header>

        <div class="product-grid">
          <article class="product">
            <div class="product-icon" aria-hidden="true">📊</div>
            <h3>ETFs</h3>
            <p>Fondos que siguen índices. Diversificación instantánea con costos bajos.</p>
            <ul class="tags">
              <li>Bajo costo</li><li>Diversificado</li><li>Largo plazo</li>
            </ul>
          </article>

          <article class="product">
            <div class="product-icon" aria-hidden="true">💵</div>
            <h3>Bonos</h3>
            <p>Instrumentos de renta fija. Flujo de cupones y fechas de vencimiento claras.</p>
            <ul class="tags">
              <li>Ingreso</li><li>Duración</li><li>Calif. riesgo</li>
            </ul>
          </article>

          <article class="product">
            <div class="product-icon" aria-hidden="true">💹</div>
            <h3>Acciones</h3>
            <p>Participación en empresas. Mayor volatilidad con potencial de crecimiento.</p>
            <ul class="tags">
              <li>Volátil</li><li>Retorno esperado</li><li>Dividendos</li>
            </ul>
          </article>

          <article class="product">
            <div class="product-icon" aria-hidden="true">🏦</div>
            <h3>Fondos comunes</h3>
            <p>Administración profesional. Estrategias para distintos perfiles.</p>
            <ul class="tags">
              <li>Gestión</li><li>Liquidez</li><li>Comisiones</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <!-- Comparador -->
    <section id="comparador" class="section container">
      <header class="section-header">
        <h2>Comparador rápido</h2>
        <p>Una guía orientativa de riesgo, plazo y objetivo típico.</p>
      </header>

      <div class="table-wrap" role="region" aria-label="Tabla comparativa">
        <table class="table">
          <thead>
            <tr>
              <th>Instrumento</th>
              <th>Riesgo</th>
              <th>Plazo sugerido</th>
              <th>Objetivo típico</th>
              <th>Liquidez</th>
              <th>Costos</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>ETFs</td>
              <td><span class="pill pill-mid">Medio</span></td>
              <td>3–5 años+</td>
              <td>Crecer capital</td>
              <td>Alta</td>
              <td>Bajos</td>
            </tr>
            <tr>
              <td>Bonos</td>
              <td><span class="pill pill-low">Bajo–Medio</span></td>
              <td>1–3 años+</td>
              <td>Ingreso</td>
              <td>Media</td>
              <td>Bajos–Medios</td>
            </tr>
            <tr>
              <td>Acciones</td>
              <td><span class="pill pill-high">Alto</span></td>
              <td>5–10 años</td>
              <td>Crecimiento</td>
              <td>Alta</td>
              <td>Medios</td>
            </tr>
            <tr>
              <td>Fondos comunes</td>
              <td><span class="pill pill-var">Variable</span></td>
              <td>Depende del fondo</td>
              <td>Mixto</td>
              <td>Alta</td>
              <td>Variables</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="legend">La información es educativa y puede no reflejar tu situación particular.</p>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <p>© <span id="year"></span> ModuMoney</p>
     
    </div>
    <script>
      document.getElementById('year').textContent = new Date().getFullYear();
    </script>
  </footer>
</body>
</html>
