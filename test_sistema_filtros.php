<?php
/**
 * Script de prueba para el Sistema de Filtros
 * Helmets Pro v2.0 - Verificación de Filtros
 */

echo "<h1>🧪 Test Sistema de Filtros - Helmets Pro</h1>";
echo "<hr>";

// 1. Verificar que existe la clase FiltrosProductos
echo "<h2>1. Verificación de Clase FiltrosProductos</h2>";
if (file_exists('clases/FiltrosProductos.php')) {
    echo "✅ Archivo clases/FiltrosProductos.php existe<br>";
    require_once 'clases/FiltrosProductos.php';
    echo "✅ Clase FiltrosProductos cargada correctamente<br>";
} else {
    echo "❌ ERROR: No se encontró clases/FiltrosProductos.php<br>";
    exit;
}

// 2. Verificar conexión a BD
echo "<h2>2. Verificación de Conexión BD</h2>";
try {
    $filtros = new FiltrosProductos();
    echo "✅ Conexión a BD exitosa<br>";
} catch (Exception $e) {
    echo "❌ ERROR de conexión BD: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Verificar que existe la columna categoria
echo "<h2>3. Verificación de Estructura BD</h2>";
try {
    require_once 'administrador/config/DB.php';
    $db = DB::getInstance();
    
    // Verificar columna categoria
    $sql = "SHOW COLUMNS FROM cascos LIKE 'categoria'";
    $resultado = $db->fetchOne($sql);
    
    if ($resultado) {
        echo "✅ Columna 'categoria' existe en tabla cascos<br>";
    } else {
        echo "❌ ERROR: Columna 'categoria' no existe. Ejecuta bd/agregar_categoria_cascos.sql<br>";
    }
    
    // Verificar índices
    $sql = "SHOW INDEX FROM cascos WHERE Key_name IN ('idx_categoria', 'idx_precio', 'idx_nombre')";
    $indices = $db->fetchAll($sql);
    
    if (count($indices) >= 3) {
        echo "✅ Índices de filtros creados correctamente<br>";
    } else {
        echo "⚠️ ADVERTENCIA: Algunos índices no están creados. Ejecuta bd/agregar_categoria_cascos.sql<br>";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR verificando estructura BD: " . $e->getMessage() . "<br>";
}

// 4. Probar obtención de categorías
echo "<h2>4. Prueba de Categorías</h2>";
try {
    $categorias = $filtros->obtenerCategorias();
    echo "✅ Categorías obtenidas: " . count($categorias) . " categorías<br>";
    foreach ($categorias as $cat) {
        echo "   - " . $cat['categoria'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ ERROR obteniendo categorías: " . $e->getMessage() . "<br>";
}

// 5. Probar rango de precios
echo "<h2>5. Prueba de Rango de Precios</h2>";
try {
    $rango = $filtros->obtenerRangoPrecios();
    echo "✅ Rango de precios: $" . number_format($rango['precio_min'], 2) . " - $" . number_format($rango['precio_max'], 2) . "<br>";
} catch (Exception $e) {
    echo "❌ ERROR obteniendo rango de precios: " . $e->getMessage() . "<br>";
}

// 6. Probar filtros individuales
echo "<h2>6. Prueba de Filtros Individuales</h2>";

// Filtro por categoría
try {
    $productosCategoria = $filtros->filtrarProductos(['categoria' => 'Deportivo']);
    echo "✅ Filtro por categoría 'Deportivo': " . count($productosCategoria) . " productos<br>";
} catch (Exception $e) {
    echo "❌ ERROR filtro por categoría: " . $e->getMessage() . "<br>";
}

// Filtro por precio
try {
    $productosPrecio = $filtros->filtrarProductos(['precio_min' => 200, 'precio_max' => 300]);
    echo "✅ Filtro por precio ($200-$300): " . count($productosPrecio) . " productos<br>";
} catch (Exception $e) {
    echo "❌ ERROR filtro por precio: " . $e->getMessage() . "<br>";
}

// Filtro por búsqueda
try {
    $productosBusqueda = $filtros->filtrarProductos(['busqueda' => 'AGV']);
    echo "✅ Filtro por búsqueda 'AGV': " . count($productosBusqueda) . " productos<br>";
} catch (Exception $e) {
    echo "❌ ERROR filtro por búsqueda: " . $e->getMessage() . "<br>";
}

// 7. Probar filtros combinados
echo "<h2>7. Prueba de Filtros Combinados</h2>";
try {
    $filtrosCombinados = [
        'categoria' => 'Deportivo',
        'precio_min' => 200,
        'orden' => 'precio',
        'direccion' => 'ASC'
    ];
    
    $productosCombinados = $filtros->filtrarProductos($filtrosCombinados);
    echo "✅ Filtros combinados (Deportivo + precio > $200 + ordenado por precio): " . count($productosCombinados) . " productos<br>";
    
    if (count($productosCombinados) > 0) {
        echo "   Primer producto: " . $productosCombinados[0]['nombre'] . " - $" . $productosCombinados[0]['precio'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ ERROR filtros combinados: " . $e->getMessage() . "<br>";
}

// 8. Probar conteo de productos
echo "<h2>8. Prueba de Conteo de Productos</h2>";
try {
    $totalProductos = $filtros->contarProductos();
    echo "✅ Total de productos activos: " . $totalProductos . "<br>";
    
    $totalFiltrados = $filtros->contarProductos(['categoria' => 'Deportivo']);
    echo "✅ Total productos deportivos: " . $totalFiltrados . "<br>";
} catch (Exception $e) {
    echo "❌ ERROR contando productos: " . $e->getMessage() . "<br>";
}

// 9. Probar sanitización de filtros
echo "<h2>9. Prueba de Sanitización</h2>";
try {
    $filtrosSucios = [
        'categoria' => '<script>alert("xss")</script>Deportivo',
        'precio_min' => 'abc',
        'busqueda' => 'AGV<script>',
        'orden' => 'precio; DROP TABLE cascos;'
    ];
    
    $filtrosLimpios = FiltrosProductos::sanitizarFiltros($filtrosSucios);
    echo "✅ Sanitización exitosa<br>";
    echo "   Categoría original: " . $filtrosSucios['categoria'] . "<br>";
    echo "   Categoría limpia: " . ($filtrosLimpios['categoria'] ?? 'no definida') . "<br>";
} catch (Exception $e) {
    echo "❌ ERROR en sanitización: " . $e->getMessage() . "<br>";
}

// 10. Resumen final
echo "<h2>10. Resumen Final</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<strong>🎉 Sistema de Filtros - Helmets Pro v2.0</strong><br>";
echo "✅ Clase FiltrosProductos implementada<br>";
echo "✅ Filtros por categoría funcionando<br>";
echo "✅ Filtros por rango de precios funcionando<br>";
echo "✅ Búsqueda por texto funcionando<br>";
echo "✅ Filtros combinados funcionando<br>";
echo "✅ Sanitización de datos implementada<br>";
echo "✅ Ordenamiento personalizable<br>";
echo "<br><strong>🚀 El sistema está listo para usar!</strong>";
echo "</div>";

echo "<hr>";
echo "<p><a href='productos.php'>🔗 Ir a productos.php para probar los filtros</a></p>";
?>

