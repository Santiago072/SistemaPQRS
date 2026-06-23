<?php
if (!defined('BASE_PATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
    define('BASE_PATH', $isRailway ? '/' : '/SistemaPQRS/');
}
?>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                        <span>Sistema PQRS</span>
                    </div>
                    <p class="footer-text">
                        Plataforma oficial de gestión de Peticiones, Quejas, Reclamos, 
                        Sugerencias y Denuncias.
                    </p>
                </div>

                <div>
                    <h4 class="footer-title">Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="#" onclick="if(typeof abrirModal === 'function'){ abrirModal(); } else { window.location.href='<?php echo BASE_PATH; ?>index.php?ruta=pqrs/tipos'; } return false;">
                                <i class="bi bi-pencil-square"></i>
                                Nueva Solicitud
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/consulta">
                                <i class="bi bi-search"></i>
                                Consultar Estado
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/login">
                                <i class="bi bi-shield-lock"></i>
                                Panel Administrador
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Marco Legal</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="https://www.secretariasenado.gov.co/senado/basedoc/ley_1755_2015.html" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-file-earmark-text"></i>
                                Ley 1755 de 2015 (Regulación PQRS)
                            </a>
                        </li>
                        <li>
                            <a href="https://www.secretariasenado.gov.co/senado/basedoc/ley_1437_2011.html" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-file-earmark-text"></i>
                                Ley 1437 de 2011 (CPACA)
                            </a>
                        </li>
                        <li>
                            <a href="https://www.secretariasenado.gov.co/senado/basedoc/ley_1581_2012.html" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-shield-check"></i>
                                Ley 1581 de 2012 (Protección de Datos)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>
                    <i class="bi bi-c-circle" style="margin-right: var(--space-1);"></i>
                    2026 Sistema PQRS - Todos los derechos reservados
                </p>
            </div>
        </div>
    </footer>
    <?php 
    // Incluir el modal de términos en todas las páginas si no existe aún en el DOM
    // (Solo se mostrará si se llama a abrirModal())
    $modalPath = __DIR__ . '/modal_terminos.php';
    if (file_exists($modalPath)) {
        include_once $modalPath;
    }
    ?>
</body>
</html>
