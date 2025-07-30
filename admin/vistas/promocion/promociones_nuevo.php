<?php

// Obtener productos activos para el select
$stmt = $pdo->query("SELECT id, nombre FROM productos WHERE estado_activo = 1 ORDER BY nombre ASC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Insertar promoción si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = $_POST['producto_id'];
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $valor = isset($_POST['valor']) && $_POST['valor'] !== '' ? $_POST['valor'] : null;
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $estado = $_POST['estado'];

    $sql = "INSERT INTO promociones (producto_id, titulo, descripcion, tipo, valor, fecha_inicio, fecha_fin, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id, $titulo, $descripcion, $tipo, $valor, $fecha_inicio, $fecha_fin, $estado]);

    header("Location: dashboard.php?vista=promocion/promociones");
    exit;
}


?>

<div class="card">
    <h2>➕ Nueva Promoción</h2>

    <form method="POST" class="formulario">
        <label>Producto:</label>
        <select name="producto_id" required>
            <option value="">Seleccionar...</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Título:</label>
        <input type="text" name="titulo" required>

        <label>Descripción:</label>
        <textarea name="descripcion"></textarea>

        <label>Tipo de promoción:</label>
        <select name="tipo" required onchange="toggleValor(this.value)">
            <option value="porcentaje">Porcentaje</option>
            <option value="fijo">Monto fijo</option>
            <option value="envio_gratis">Envío Gratis</option>
        </select>

        <label id="valorLabel">Valor:</label>
        <input type="number" step="0.01" name="valor" id="valorInput">

        <label>Fecha de inicio:</label>
        <input type="date" name="fecha_inicio" required>

        <label>Fecha de fin:</label>
        <input type="date" name="fecha_fin" required>

        <label>Estado:</label>
        <select name="estado">
            <option value="activo" selected>Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>

        <button type="submit">Guardar promoción</button>
    </form>
</div>

<script>
    function toggleValor(tipo) {
        const label = document.getElementById('valorLabel');
        const input = document.getElementById('valorInput');

        if (tipo === 'envio_gratis') {
            label.style.display = 'none';
            input.style.display = 'none';
            input.removeAttribute('required');
        } else {
            label.style.display = 'block';
            input.style.display = 'block';
            input.setAttribute('required', 'required');
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        toggleValor(document.querySelector('[name="tipo"]').value);
    });
</script>

<style>
    .formulario {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 500px;
    }

    .formulario label {
        font-weight: bold;
    }

    .formulario input, .formulario textarea, .formulario select {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .formulario button {
        background-color: var(--color-primario, #007bff);
        color: white;
        padding: 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    .formulario button:hover {
        background-color: #0056b3;
    }
</style>
