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
    
    .volunteers-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        background-color: #f5f5dc; /* Beige claro de fondo */
    }
    
    .volunteers-container h1 {
        text-align: center;
        color: #2e8b57; /* Verde bosque */
        font-size: 2.5rem;
        margin-bottom: 30px;
        position: relative;
    }
    
    .volunteers-container h1:after {
        content: "";
        display: block;
        width: 100px;
        height: 4px;
        background: #ff8c42; /* Naranja cálido */
        margin: 10px auto;
    }
    
    .volunteer-info {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .volunteer-info p {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .volunteers-container h2 {
        color: #2e8b57; /* Verde bosque */
        margin: 25px 0 15px;
        font-size: 1.8rem;
    }
    
    .volunteer-info ul {
        list-style-type: none;
        padding-left: 0;
    }
    
    .volunteer-info li {
        padding: 10px 0 10px 30px;
        position: relative;
        font-size: 1.05rem;
        border-bottom: 1px solid #eee;
    }
    
    .volunteer-info li:before {
        content: "🐾";
        position: absolute;
        left: 0;
    }
    
    .cta-volunteer {
        text-align: center;
        margin-top: 40px;
        padding: 30px;
        background: #f5f5dc; /* Beige claro */
        border-radius: 10px;
    }
    
    .cta-volunteer p {
        font-size: 1.3rem;
        margin-bottom: 20px;
        color: #555;
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

    .testimonials {
        margin: 40px 0;
        padding: 20px;
        background: rgba(46, 139, 87, 0.1); /* Verde bosque muy claro */
        border-radius: 10px;
    }
    
    .testimonial-item {
        margin: 20px 0;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }
    
    .testimonial-item p {
        font-style: italic;
    }
    
    .testimonial-author {
        font-weight: bold;
        text-align: right;
        color: #2e8b57; /* Verde bosque */
    }
    
    .img-section {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        margin: 30px 0;
        gap: 15px;
    }
    
    .img-container {
        width: 30%;
        min-width: 250px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .img-container:hover {
        transform: scale(1.03);
    }
    
    .img-container img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
    }
    
    .img-caption {
        background: white;
        padding: 10px;
        text-align: center;
        font-size: 0.9rem;
        color: #555;
    }
    
    .hero-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .activity-section {
        display: flex;
        align-items: center;
        margin: 25px 0;
        gap: 20px;
    }
    
    .activity-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 10px;
        flex-shrink: 0;
    }
    
    .benefits-box {
        background: rgba(46, 139, 87, 0.1); /* Verde bosque muy claro */
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .benefits-box h3 {
        color: #2e8b57; /* Verde bosque */
        margin-top: 0;
    }
    
    @media (max-width: 768px) {
        .volunteers-container {
            padding: 10px;
        }
        
        .volunteer-info {
            padding: 20px;
        }
        
        .activity-section {
            flex-direction: column;
        }
        
        .activity-img {
            width: 100%;
            height: 200px;
        }
        
        .img-container {
            width: 100%;
        }
    }
</style>

<div class="volunteers-container">
    <h1>Únete como Voluntario</h1>
    
    <img src="assets/img/voluntarios/voluntarios-1.jpg" alt="Voluntarios cuidando animales" class="hero-image">
    
    <div class="volunteer-info">
        <p>En Huellitas en la luna, nuestros voluntarios son el corazón de nuestra organización. Si amas a los animales y quieres ayudar, ¡te necesitamos! Cada hora que dediques marca la diferencia en la vida de nuestros peludos amigos.</p>
        
        <div class="img-section">
            <div class="img-container">
                <img src="assets/img/voluntarios/voluntarios-2.jpg" alt="Voluntario paseando perro">
                <div class="img-caption">Nuestros voluntarios disfrutan paseando a los perros</div>
            </div>
            <div class="img-container">
                <img src="assets/img/voluntarios/voluntarios-3.jpg" alt="Voluntario cuidando gato">
                <div class="img-caption">Cuidado y cariño para todos nuestros animales</div>
            </div>
            <div class="img-container">
                <img src="assets/img/voluntarios/voluntarios-4.jpg" alt="Evento de adopción">
                <div class="img-caption">Participación en eventos de adopción</div>
            </div>
        </div>
        
        <div class="benefits-box">
            <h3>Beneficios de ser voluntario:</h3>
            <ul>
                <li>Experiencia práctica trabajando con animales</li>
                <li>Formación continua sobre cuidado animal</li>
                <li>Comunidad de personas con tus mismos intereses</li>
                <li>Posibilidad de referencia laboral</li>
                <li>La satisfacción de ayudar a quienes más lo necesitan</li>
            </ul>
        </div>
        
        <h2>Áreas donde puedes ayudar:</h2>
        
        <div class="activity-section">
            <img src="assets/img/voluntarios/voluntarios-5.jpg" alt="Cuidado directo de animales" class="activity-img">
            <div>
                <h3>Cuidado directo de animales</h3>
                <p>Alimentación, medicación y atención básica para nuestros residentes. Aprenderás técnicas profesionales de cuidado animal.</p>
            </div>
        </div>
        
        <div class="activity-section">
            <img src="assets/img/voluntarios/voluntarios-6.jpg" alt="Paseo de perros" class="activity-img">
            <div>
                <h3>Paseo y socialización</h3>
                <p>Los paseos son esenciales para el bienestar físico y emocional de nuestros perros. ¡También es la actividad más divertida!</p>
            </div>
        </div>
        
        <div class="activity-section">
            <img src="assets/img/voluntarios/voluntarios-7.jpg" alt="Limpieza de instalaciones" class="activity-img">
            <div>
                <h3>Limpieza y mantenimiento</h3>
                <p>Mantener las instalaciones limpias es fundamental para la salud de los animales. Trabajo en equipo garantizado.</p>
            </div>
        </div>
        
        <div class="activity-section">
            <img src="assets/img/voluntarios/voluntarios-8.jpg" alt="Evento de adopción" class="activity-img">
            <div>
                <h3>Eventos de adopción</h3>
                <p>Ayuda a organizar y participar en eventos donde los animales encuentran sus familias para siempre.</p>
            </div>
        </div>
        
        <h2>Requisitos:</h2>
        <ul>
            <li>Mayor de 18 años (o acompañado de un adulto responsable)</li>
            <li>Amor genuino por los animales y respeto por su bienestar</li>
            <li>Compromiso y responsabilidad con tus turnos asignados</li>
            <li>Disponibilidad de al menos 4 horas semanales</li>
            <li>Asistencia a la capacitación inicial</li>
        </ul>
        
        <div class="testimonials">
            <h2>Historias de nuestros voluntarios</h2>
            
            <div class="testimonial-item">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                    <img src="assets/img/voluntarios/voluntarios-9.jpg" alt="María" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <p>"Ser voluntaria en Huellitas en la luna ha sido una de las experiencias más gratificantes de mi vida. Ver cómo los animales recuperan la confianza y encuentran hogares llenos de amor no tiene precio."</p>
                        <div class="testimonial-author">- María, voluntaria desde 2020</div>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-item">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                    <img src="assets/img/voluntarios/voluntarios-10.jpg" alt="Carlos" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <p>"Al principio solo venía los fines de semana, pero terminé tan enamorado de la labor que ahora coordino el equipo de paseos. ¡Los perros son mis mejores amigos!"</p>
                        <div class="testimonial-author">- Carlos, coordinador de voluntarios</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="img-section">
            <div class="img-container">
                <img src="assets/img/voluntarios/voluntarios-11.jpg" alt="Grupo de voluntarios">
                <div class="img-caption">Nuestro increíble equipo de voluntarios</div>
            </div>
            <div class="img-container">
                <img src="assets/img/voluntarios/voluntarios-12.jpg" alt="Historia de éxito">
                <div class="img-caption">Max encontró hogar gracias a nuestros voluntarios</div>
            </div>
            <div class="img-container">
                <img src="assets/img/voluntarios/voluntarios-13.jpg" alt="Capacitación">
                <div class="img-caption">Sesiones de capacitación para nuevos voluntarios</div>
            </div>
        </div>
        
        <div class="cta-volunteer">
            <p>¿Listo para embarcarte en esta aventura llena de amor y patitas?</p>
            <a href="registro.php" class="btn">Regístrate como voluntario</a>
            <p style="margin-top: 15px; font-size: 0.9rem;">O contáctanos en <a href="mailto:voluntarios@huellitasenlaluna.org" style="color: #ff8c42;">voluntarios@huellitasenlaluna.org</a> para más información</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
