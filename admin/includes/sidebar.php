<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$currentPath = $_SERVER['PHP_SELF'];
$inSubfolder = strpos($currentPath, '/solicitudes/') !== false;
$basePath = $inSubfolder ? '../' : '';
?>
<div id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h4>Adoptame Saladillo</h4>
        <!-- Botón de cierre para la vista móvil -->
        <button class="close-sidebar d-md-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="user-info mb-3">
        <?php if(isset($_SESSION['admin_nombre'])): ?>
            <p class="mb-0">Bienvenido,</p>
            <p class="fw-bold"><?php echo $_SESSION['admin_nombre']; ?></p>
        <?php endif; ?>
    </div>
    
    <div class="menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="<?php echo $basePath; ?>/mascotas/usuario/index.php">
                    <i class="fas fa-globe me-2"></i> Web
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="<?php echo $basePath; ?>/mascotas/admin/index.php">
                    <i class="fas fa-paw me-2"></i> Mascotas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="<?php echo $basePath; ?>registrar_mascota.php">
                    <i class="fas fa-plus-circle me-2"></i> Registrar Mascotas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="<?php echo $basePath; ?>solicitudes/index.php">
                    <i class="fas fa-clipboard-list me-2"></i> Solicitudes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="<?php echo $basePath; ?>chat/index.php">
                    <i class="fas fa-comments me-2"></i> Chats Activos
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link d-flex align-items-center text-danger" href="<?php echo $basePath; ?>auth/logout/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const closeSidebar = document.querySelector('.close-sidebar');
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    // Función para abrir/cerrar sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
        overlay.classList.toggle('active');
    }

    sidebarToggle.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Cerrar sidebar en enlaces en la parte móvil
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                toggleSidebar();
            }
        });
    });
});
</script>