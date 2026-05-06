<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de PQRS - Peticiones, Quejas, Reclamos y Sugerencias. Radique y consulte el estado de sus solicitudes de forma fácil y segura.">
    <meta name="keywords" content="PQRS, peticiones, quejas, reclamos, sugerencias, denuncias, servicios públicos, Neiva">
    <meta name="author" content="Sistema PQRS">
    <title>Sistema PQRS - Gestión de Peticiones, Quejas, Reclamos y Sugerencias</title>

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Hoja de estilos única del sistema -->
    <link rel="stylesheet" href="css/estilos.css">
<base target="_blank">
</head>
<body>

    <!-- ============================================
         HEADER / NAVEGACIÓN
         ============================================ -->
    <header class="header">
        <div class="container header-container">
            <!-- Logo -->
            <a href="index.html" class="logo" aria-label="Inicio - Sistema PQRS">
                <span class="logo-icon" aria-hidden="true">
                    <i class="bi bi-clipboard-data"></i>
                </span>
                <span>Sistema PQRS</span>
            </a>

            <!-- Login Admin (discreto, esquina superior derecha) -->
            <nav class="nav-admin" aria-label="Navegación administrativa">
                <a href="login.html" class="btn btn-outline" aria-label="Acceder al panel de administración">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Iniciar Sesión</span>
                </a>
            </nav>
        </div>
    </header>

    <main>
        <!-- ============================================
             HERO SECTION
             ============================================ -->
        <section class="hero" aria-labelledby="hero-title">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="bi bi-building" aria-hidden="true"></i>
                        <span>Servicio público de atención ciudadana</span>
                    </div>

                    <h1 id="hero-title" class="hero-title">
                        Tu voz cuenta.<br>
                        <span style="opacity: 0.9;">Gestiona tus PQRS de forma transparente</span>
                    </h1>

                    <p class="hero-description">
                        Sistema oficial para la recepción, seguimiento y respuesta de Peticiones, 
                        Quejas, Reclamos, Sugerencias y Denuncias. Cumplimiento garantizado 
                        con los tiempos legales establecidos.
                    </p>

                    <div class="hero-actions">
 <!-- Botón Nueva Solicitud - Abre modal en la misma página -->
<button type="button" class="btn btn-primary" onclick="abrirModal()" aria-label="Crear una nueva solicitud PQRS">
    <i class="bi bi-pencil-square" aria-hidden="true"></i>
    <span>Nueva Solicitud</span>
</button>

                        <!-- Botón Consultar Estado -->
                        <a href="consultar.html" class="btn btn-secondary" aria-label="Consultar el estado de una solicitud existente">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <span>Consultar Estado</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================
             SECCIÓN: ¿QUÉ ES PQRS?
             ============================================ -->
        <section class="section section-alt" aria-labelledby="que-es-title">
            <div class="container">
                <header class="section-header">
                    <span class="section-tag">Información</span>
                    <h2 id="que-es-title" class="section-title">¿Qué es el sistema PQRS?</h2>
                    <p class="section-description">
                        PQRS son las siglas de <strong>Peticiones, Quejas, Reclamos y Sugerencias</strong>, 
                        mecanismos constitucionales que permiten a los ciudadanos comunicarse con las 
                        entidades públicas y privadas que prestan servicios públicos.
                    </p>
                </header>

                <div class="cards-grid" role="list">
                    <!-- Petición -->
                    <article class="card" role="listitem">
                        <div class="card-icon peticion" aria-hidden="true">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <h3 class="card-title">Petición</h3>
                        <p class="card-description">
                            Derecho fundamental de toda persona para presentar solicitudes respetuosas 
                            ante autoridades y entidades por motivos de interés general o particular.
                        </p>
                    </article>

                    <!-- Queja -->
                    <article class="card" role="listitem">
                        <div class="card-icon queja" aria-hidden="true">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                        <h3 class="card-title">Queja</h3>
                        <p class="card-description">
                            Manifestación de inconformidad por la conducta de un servidor público 
                            o particular en el ejercicio de sus funciones.
                        </p>
                    </article>

                    <!-- Reclamo -->
                    <article class="card" role="listitem">
                        <div class="card-icon reclamo" aria-hidden="true">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h3 class="card-title">Reclamo</h3>
                        <p class="card-description">
                            Derecho del usuario o consumidor para exigir el cumplimiento de derechos 
                            vulnerados o reclamar por la prestación inadecuada de un servicio.
                        </p>
                    </article>

                    <!-- Sugerencia -->
                    <article class="card" role="listitem">
                        <div class="card-icon sugerencia" aria-hidden="true">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <h3 class="card-title">Sugerencia</h3>
                        <p class="card-description">
                            Propuesta constructiva para mejorar los procesos, servicios o procedimientos 
                            de la entidad, orientada al beneficio colectivo.
                        </p>
                    </article>

                    <!-- Denuncia -->
                    <article class="card" role="listitem">
                        <div class="card-icon denuncia" aria-hidden="true">
                            <i class="bi bi-megaphone"></i>
                        </div>
                        <h3 class="card-title">Denuncia</h3>
                        <p class="card-description">
                            Comunicación de posibles irregularidades, actos de corrupción o violaciones 
                            a la normatividad por parte de servidores públicos o contratistas.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============================================
             SECCIÓN: PROCESO DE RADICACIÓN
             ============================================ -->
        <section class="section" aria-labelledby="proceso-title">
            <div class="container">
                <header class="section-header">
                    <span class="section-tag">Proceso</span>
                    <h2 id="proceso-title" class="section-title">¿Cómo radicar una PQRS?</h2>
                    <p class="section-description">
                        Sigue estos sencillos pasos para registrar tu solicitud y hacer seguimiento 
                        en tiempo real hasta obtener una respuesta formal.
                    </p>
                </header>

                <div class="timeline" aria-label="Pasos del proceso de radicación">
                    <!-- Paso 1 -->
                    <div class="timeline-item">
                        <span class="timeline-number" aria-hidden="true">1</span>
                        <div class="timeline-content">
                            <h3 class="timeline-title">Selecciona el tipo de solicitud</h3>
                            <p class="timeline-text">
                                Elige entre Petición, Queja, Reclamo, Sugerencia o Denuncia según 
                                la naturaleza de tu requerimiento.
                            </p>
                        </div>
                    </div>

                    <!-- Paso 2 -->
                    <div class="timeline-item">
                        <span class="timeline-number" aria-hidden="true">2</span>
                        <div class="timeline-content">
                            <h3 class="timeline-title">Completa el formulario</h3>
                            <p class="timeline-text">
                                Ingresa tus datos como persona natural, jurídica o de forma anónima. 
                                Describe detalladamente tu solicitud y adjunta documentos si es necesario.
                            </p>
                        </div>
                    </div>

                    <!-- Paso 3 -->
                    <div class="timeline-item">
                        <span class="timeline-number" aria-hidden="true">3</span>
                        <div class="timeline-content">
                            <h3 class="timeline-title">Recibe tu código único</h3>
                            <p class="timeline-text">
                                El sistema genera automáticamente un número de radicado con formato 
                                <strong>PQRS-AAAA-MMMM-NNN</strong> que identifica tu caso de forma única.
                            </p>
                        </div>
                    </div>

                    <!-- Paso 4 -->
                    <div class="timeline-item">
                        <span class="timeline-number" aria-hidden="true">4</span>
                        <div class="timeline-content">
                            <h3 class="timeline-title">Seguimiento en tiempo real</h3>
                            <p class="timeline-text">
                                Consulta el estado de tu solicitud en cualquier momento usando tu 
                                código de radicado o correo electrónico registrado.
                            </p>
                        </div>
                    </div>

                    <!-- Paso 5 -->
                    <div class="timeline-item">
                        <span class="timeline-number" aria-hidden="true">5</span>
                        <div class="timeline-content">
                            <h3 class="timeline-title">Recibe respuesta formal</h3>
                            <p class="timeline-text">
                                Una vez resuelta, recibirás una respuesta formal por correo electrónico 
                                y podrás consultarla en el portal público de seguimiento.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================
             SECCIÓN: TIEMPOS DE RESPUESTA LEGALES
             ============================================ -->
        <section class="section section-alt" aria-labelledby="tiempos-title">
            <div class="container">
                <header class="section-header">
                    <span class="section-tag">Marco Legal</span>
                    <h2 id="tiempos-title" class="section-title">Tiempos de respuesta legales</h2>
                    <p class="section-description">
                        De acuerdo con la <strong>Ley 1755 de 2015</strong> y la <strong>Ley 1437 de 2011</strong>, 
                        las entidades están obligadas a responder dentro de los siguientes términos:
                    </p>
                </header>

                <div class="legal-table-container">
                    <table class="legal-table">
                        <thead>
                            <tr>
                                <th scope="col">Tipo de Solicitud</th>
                                <th scope="col">Término Legal</th>
                                <th scope="col">Marco Normativo</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Petición</strong></td>
                                <td>15 días hábiles</td>
                                <td>Ley 1755 de 2015, Art. 13</td>
                                <td>
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Vigente
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Queja</strong></td>
                                <td>15 días hábiles</td>
                                <td>Ley 1755 de 2015, Art. 14</td>
                                <td>
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Vigente
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Reclamo</strong></td>
                                <td>15 días hábiles</td>
                                <td>Ley 1437 de 2011, Art. 56</td>
                                <td>
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Vigente
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Sugerencia</strong></td>
                                <td>15 días hábiles</td>
                                <td>Ley 1755 de 2015, Art. 15</td>
                                <td>
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Vigente
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Denuncia</strong></td>
                                <td>10 días hábiles</td>
                                <td>Ley 1474 de 2011, Art. 7</td>
                                <td>
                                    <span class="badge badge-yellow">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Prioritaria
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: var(--space-6); text-align: center;">
                    <p style="color: var(--color-gray-500); font-size: var(--font-size-sm);">
                        <i class="bi bi-info-circle" style="color: var(--color-primary); margin-right: var(--space-2);"></i>
                        Los términos pueden ampliarse por una sola vez hasta por el mismo periodo, 
                        previa justificación escrita al solicitante.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================
             SECCIÓN: CTA FINAL
             ============================================ -->
        <section class="cta-section" aria-labelledby="cta-title">
            <div class="container">
                <div class="cta-content">
                    <h2 id="cta-title" class="cta-title">¿Necesitas presentar una PQRS?</h2>
                    <p class="cta-description">
                        Radica tu solicitud ahora y recibe un código único para hacer seguimiento. 
                        Nuestro equipo se compromete a responder dentro de los términos legales establecidos.
                    </p>
                    <div class="cta-buttons">
                        <a href="terminos.html" class="btn btn-white" aria-label="Crear nueva solicitud PQRS">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                            <span>Nueva Solicitud</span>
                        </a>
                        <a href="consultar.html" class="btn btn-outline-white" aria-label="Consultar estado de solicitud existente">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <span>Consultar Estado</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Marca -->
                <div>
                    <div class="footer-brand">
                        <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                        <span>Sistema PQRS</span>
                    </div>
                    <p class="footer-text">
                        Plataforma oficial de gestión de Peticiones, Quejas, Reclamos, 
                        Sugerencias y Denuncias. Garantizando transparencia, trazabilidad 
                        y cumplimiento legal en la atención ciudadana.
                    </p>
                </div>

                <!-- Enlaces rápidos -->
                <div>
                    <h4 class="footer-title">Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="terminos.html">
                                <i class="bi bi-pencil-square"></i>
                                Nueva Solicitud
                            </a>
                        </li>
                        <li>
                            <a href="consultar.html">
                                <i class="bi bi-search"></i>
                                Consultar Estado
                            </a>
                        </li>
                        <li>
                            <a href="login.html">
                                <i class="bi bi-shield-lock"></i>
                                Panel Administrador
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Marco legal -->
                <div>
                    <h4 class="footer-title">Marco Legal</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=62567" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-file-earmark-text"></i>
                                Ley 1755 de 2015
                            </a>
                        </li>
                        <li>
                            <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=42761" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-file-earmark-text"></i>
                                Ley 1437 de 2011
                            </a>
                        </li>
                        <li>
                            <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=44306" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-file-earmark-text"></i>
                                Ley 1474 de 2011
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>
                    <i class="bi bi-c-circle" style="margin-right: var(--space-1);"></i>
                    2026 Sistema PQRS - Todos los derechos reservados | Diseñado para cumplimiento legal y transparencia institucional
                </p>
            </div>
        </div>
    </footer>
    <?php include 'includes/modal_terminos.php'; ?>
</body>
</html>