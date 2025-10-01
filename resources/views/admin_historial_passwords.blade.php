<?php
session_start();
require_once 'config/database.php';
// Solo permitir acceso a administradores
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->query("SELECT c.id, u.nombre, u.apellidos, u.correo, c.fecha, c.ip, c.user_agent 
    FROM cambios_password c 
    JOIN usuarios u ON c.usuario_id = u.id 
    ORDER BY c.fecha DESC LIMIT 100");
$cambios = $stmt->fetchAll(PDO::FETCH_ASSOC);
@include('includes.header');
?>
<div class="container mt-5">
    <h3>Historial de cambios de contraseña</h3>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Fecha</th>
                <th>IP</th>
                <th>Navegador</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cambios as $cambio): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cambio['nombre'] . ' ' . $cambio['apellidos']); ?></td>
                    <td><?php echo htmlspecialchars($cambio['correo']); ?></td>
                    <td><?php echo htmlspecialchars($cambio['fecha']); ?></td>
                    <td><?php echo htmlspecialchars($cambio['ip']); ?></td>
                    <td><?php echo htmlspecialchars($cambio['user_agent']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php @include('includes.footer'); ?>