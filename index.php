<?php
require_once 'includes/header.php';
?>

<style>
    :root {
        --primary-color: #2e8b57; /* Verde bosque */
        --secondary-color: #ff8c42; /* Naranja cálido */
        --dark-color: #333;
        --light-color: #f5f5dc; /* Beige claro */
        --success-color: #28a745;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--dark-color);
        background-color: var(--light-color); /* Changed to beige claro */
    }

    .btn {
        display: inline-block;
        background: var(--primary-color); /* Verde bosque */
        color: white;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        width: 100%;
        transition: background 0.3s ease, transform 0.2s ease;
    }
</style>

<main>
    <!-- Carrusel -->
    <section>
        <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="assets/img/carrusel/mini-perro.jpg" class="d-block w-100" alt="Rescate animal" style="height: 600px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block">
                        <a href="donaciones.php" class="btn btn-custom">Donaciones</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/img/carrusel/gatos.jpg" class="d-block w-100" alt="Adopciones" style="height: 600px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block">
                        <a href="adopciones.php" class="btn btn-custom">Adopciones Responsables</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/img/carrusel/perro-gato.jpg" class="d-block w-100" alt="Voluntariado" style="height: 600px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block">
                        <a href="voluntarios.php" class="btn btn-custom">Únete como Voluntario</a>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Sección Info con imagen de fondo -->
    <section class="info-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><span class="text-highlight" style="color: #A4B465;">Huellitas en la luna:</span> Amor que rescata vidas</h2>
                    <p class="lead">Somos una fundación dedicada a proteger, cuidar y dar una nueva oportunidad a animales indefensos. Con amor, compromiso y esperanza, rescatamos, rehabilitamos y encontramos hogares responsables para cada uno de ellos.</p>
                </div>
                <div class="col-md-6 text-center">
                    <img src="assets/img/veterinario.png" alt="Veterinario" class="img-fluid" style="max-height: 500px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Qué hacemos -->
    <section class="services-section py-5">
        <div class="container">
            <h2 class="text-center mb-5">¿Qué hace <span class="text-highlight" style="color: #A4B465";>Huellitas en la luna</span>?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 zoom-effect">
                        <img src="assets/img/cartas/adopciones.jpg" class="card-img-top" alt="Adopciones">
                        <div class="card-body">
                            <h5 class="card-title">Adopciones</h5>
                            <p class="card-text"><b>"Encuentra a tu compañero perfecto" </b> <br>
                                Nuestro proceso de adopción responsable incluye: <br>

                                🐕‍🦺 Animales desparasitados, esterilizados y con vacunas al día <br>

                                📝 Evaluación de compatibilidad entre adoptante y mascota <br>

                                🏡 Seguimiento post-adopción por 6 meses <br>

                                📚 Asesoría gratuita sobre cuidado animal <br>

                                ¡Más de 300 historias de éxito! Conoce a nuestros peluditos listos para llenar tu hogar de amor.</p>
                            <a href="adopciones.php" class="btn btn-custom">Ver animales</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 zoom-effect">
                        <img src="assets/img/cartas/rehabilitacion.jpg" class="card-img-top" alt="Donaciones">
                        <div class="card-body">
                            <h5 class="card-title">Donaciones</h5>
                            <p class="card-text"><b>"Tu apoyo transforma vidas"</b> <br>
                                En Huellitas en la luna Fundación, cada donación marca la diferencia. Con tu contribución: <br>

                                🏥 Cubrimos tratamientos veterinarios y medicinas <br>

                                🥫 Proveemos alimentación de calidad para nuestros rescatados <br>

                                🛏️ Mejoramos nuestros refugios y áreas de recuperación <br>

                                🚗 Mantenemos nuestra unidad de rescate móvil <br>

                                Aceptamos donaciones económicas, alimentos, medicinas y materiales. Todos los recursos son auditados y aplicados directamente al cuidado animal.</p>
                            <a href="donaciones.php" class="btn btn-custom">Conocer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 zoom-effect">
                        <img src="assets/img/cartas/rescate.jpg" class="card-img-top" alt="Voluntariado">
                        <div class="card-body">
                            <h5 class="card-title">Voluntariado</h5>
                            <p class="card-text"><b>"Únete a nuestro equipo salvavidas"</b><br>
                                Como voluntario en Huellitas en la luna puedes: <br>

                                🐾 Participar en rescates y rehabilitación <br>

                                🏡 Ser hogar temporal (transito) <br>

                                📸 Ayudar en fotografía y redes sociales <br>

                                🎨 Colaborar en eventos y campañas <br>

                                🛠️ Apoyar en mantenimiento de refugios <br>

                                No necesitas experiencia previa, solo ganas de ayudar. Ofrecemos capacitación y certificación.</p>
                            <a href="voluntarios.php" class="btn btn-custom">Conoce más</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <!--GALERIA-->
     <section class="gallery">
        <div class="container">
          <br><br>
          <center><h1>Gale<b style="color:  #A4B465">ría</b></h1></center>
          <br><br>
          <div class="row">
            <div class="col-md-4 zoom-effect">
              <center>
              <img src="assets/img/Galeria/Perro-cono.jpg"
              width="500px" height="300px" alt="">
              <br><br>
              </center>
            </div>
            <div class="col-md-8 zoom-effect">
              <center>
                <img src="assets/img/Galeria/gaticos.jpg" width="500px" height="300px" alt="">
              </center>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 zoom-effect">
              <center>
              <img src="assets/img/Galeria/cateter.jpg" width="100%" height="300px" alt="">
            <br><br>
            </center>
            </div>
            <div class="col-md-4 zoom-effect">
              <center>
              <img src="assets/img/Galeria/gsat.jpg" width="100%" height="300px" alt="">
            <br><br>
            </center>
            </div>
            <div class="col-md-4 zoom-effect">
              <center>
              <img src="assets/img/Galeria/veteri.jpg" width="100%" height="300px" alt="">
            <br><br>
            </center>
            </div>
          </div>
        </div>
      </section>

    <!-- Testimonios -->
<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Testimonios de <span class="text-highlight" style="color: #A4B465";>Huellitas en la luna</span></h2>
        <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                
                <div class="carousel-item active">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100 testimonial-card">
                                <img src="assets/img/testimonios/1.jpg" class="card-img-top testimonial-img" alt="Testimonio 1" height="310px">
                                <div class="card-body">
                                    <h5>Laura & Max (5 años)</h5>
                                    <p class="testimonial-text">"Adoptar a Max fue la mejor decisión de mi vida. Es un compañero fiel y amoroso."</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 testimonial-card">
                                <img src="assets/img/testimonios/2.jpg" class="card-img-top testimonial-img" alt="Testimonio 2" height="310px">
                                <div class="card-body">
                                    <h5>Andrea & Luna (2 años)</h5>
                                    <p class="testimonial-text">"Desde que Luna llegó a casa, todo es alegría. Su amor incondicional nos cambió el corazón."</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 testimonial-card">
                                <img src="assets/img/testimonios/3.jpg" class="card-img-top testimonial-img" alt="Testimonio 3" height="310px">
                                <div class="card-body">
                                    <h5>Camila & Toby (3 años)</h5>
                                    <p class="testimonial-text">"Toby es un torbellino de energía y ternura. No sabíamos cuánto lo necesitábamos hasta que llegó."</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100 testimonial-card">
                                <img src="assets/img/testimonios/4.jpg" class="card-img-top testimonial-img" alt="Testimonio 4" height="310px">
                                <div class="card-body">
                                    <h5>Valentina & Simba (7 años)</h5>
                                    <p class="testimonial-text">"Adoptar un perrito adulto como Simba fue maravilloso. Su gratitud y cariño son infinitos."</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 testimonial-card">
                                <img src="assets/img/testimonios/5.jpg" class="card-img-top testimonial-img" alt="Testimonio 5" height="310px">
                                <div class="card-body">
                                    <h5>Juan David & Kira (3 años)</h5>
                                    <p class="testimonial-text">"Kira llegó cuando más la necesitábamos. Nos llenó de amor y esperanza."</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 testimonial-card">
                                <img src="assets/img/testimonios/6.jpg" class="card-img-top testimonial-img" alt="Testimonio 6" height="310px">
                                <div class="card-body">
                                    <h5>Nataniel & Rocky (6 meses)</h5>
                                    <p class="testimonial-text">"Con solo seis meses, Rocky ya se robó nuestros corazones. ¡Es un travieso adorable!"</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Controles del carrusel -->
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </div>
</section>
    
    <!-- UBICACIÓN Y ESTADÍSTICAS -->
<section class="ubicacion-estadisticas py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 mb-4 mb-md-0">
                <h1 class="text-center mb-4">Encuéntranos <span style="color: #2e8b57;">Aquí</span></h1>
                <div class="ratio ratio-16x9">
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1986.9360320782437!2d-74.9520321!3d5.1243076!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e40b39e48501e29%3A0x1c6cda6a51c2ed7d!2sPollos%20Checho!5e0!3m2!1ses-419!2sco!4v1762014279414!5m2!1ses-419!2sco" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            
            <!-- Estadísticas (ocupará 6 columnas en pantallas medianas/grandes) -->
            <div class="col-lg-6 col-md-6">
                <div class="stats-section p-4 rounded" style="background-color: #A4B465;">
                    <div class="row text-white">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <h3 class="display-4">+500</h3>
                            <p class="lead">Animales rescatados</p>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <h3 class="display-4">+300</h3>
                            <p class="lead">Adopciones exitosas</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="display-4">50</h3>
                            <p class="lead">Voluntarios activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



</main>

<?php
require_once 'includes/footer.php';
?>