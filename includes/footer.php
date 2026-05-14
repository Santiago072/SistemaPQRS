<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <i class="bi bi-clipboard-data"></i>
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
                        <li><a href="<?php echo BASE_URL; ?>pqrs/tipos.php"><i class="bi bi-pencil-square"></i> Nueva Solicitud</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pqrs/consulta_pqrs.php"><i class="bi bi-search"></i> Consultar Estado</a></li>
                        <li><a href="<?php echo BASE_URL; ?>administrador/login.php"><i class="bi bi-shield-lock"></i> Panel Administrador</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Marco Legal</h4>
                    <ul class="footer-links">
                        <li><a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=62567" target="_blank"><i class="bi bi-file-earmark-text"></i> Ley 1755 de 2015</a></li>
                        <li><a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=42761" target="_blank"><i class="bi bi-file-earmark-text"></i> Ley 1437 de 2011</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p><i class="bi bi-c-circle"></i> 2026 Sistema PQRS - Todos los derechos reservados</p>
            </div>
        </div>
    </footer>
</body>
</html>