<?php
require 'vendor/autoload.php';

use Automattic\WooCommerce\Client;

$woocommerce = new Client(
    'https://themerchstudio.cl',
    'ck_a79c16ece07a02f239a3f19274ee1ffb5130944d',
    'cs_158ede5f48f48cbf06abfdceb7da3fb5d1df6b0c',
    ['version' => 'wc/v3']
);

// Configuración del archivo de progreso
$progressFile = 'progress.log';

// Cargar progreso anterior
function loadProgress($filePath) {
    if (file_exists($filePath)) {
        return array_map('trim', file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }
    return [];
}

// Guardar progreso
function saveProgress($filePath, $sku) {
    file_put_contents($filePath, $sku . PHP_EOL, FILE_APPEND);
}

// Descargar CSV
function downloadCSV($url, $filePath) {
    $ch = curl_init($url);
    $fp = fopen($filePath, 'w+');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_exec($ch);

    if (curl_errno($ch)) {
        echo "Error al descargar el archivo: " . curl_error($ch) . "\n";
        curl_close($ch);
        fclose($fp);
        exit(1);
    }

    curl_close($ch);
    fclose($fp);
    echo "Archivo descargado exitosamente: $filePath\n";
}

// Cargar productos del CSV
function loadProductsFromCSV($filePath, $limit = null) {
    $products = [];
    if (($handle = fopen($filePath, "r")) !== false) {
        $headers = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $products[] = array_combine($headers, $data);
            if ($limit !== null && count($products) >= $limit) {
                break;
            }
        }
        fclose($handle);
    }
    return $products;
}

// Crear categoría si no existe
function ensureCategoryExists($woocommerce, $categoryName) {
    $existingCategories = $woocommerce->get('products/categories', ['search' => $categoryName]);
    if (count($existingCategories) > 0) {
        return $existingCategories[0]->id;
    }
    $newCategory = $woocommerce->post('products/categories', ['name' => $categoryName]);
    return $newCategory->id;
}

// Limpiar y procesar categorías
function processCategories($woocommerce, $rawCategories) {
    $categories = [];
    $rawCategories = preg_replace('/\s*([,;])\s*/', '$1', $rawCategories);
    $categoryNames = preg_split('/[,;]/', $rawCategories);
    $categoryNames = array_unique(array_filter($categoryNames));

    foreach ($categoryNames as $categoryName) {
        $categoryId = ensureCategoryExists($woocommerce, trim($categoryName));
        $categories[] = ['id' => $categoryId];
    }

    return $categories;
}

// Verificar si un SKU ya existe
function getProductBySKU($woocommerce, $sku) {
    $products = $woocommerce->get('products', ['sku' => $sku]);
    return count($products) > 0 ? $products[0] : null;
}

// Archivo CSV
$csvUrl = 'https://globalpromoitems.com/tienda/chile/index.php?controller=SBWebService&id=H&customer=3699&token=9593905337';
$localFile = 'globalcatalogwoo.csv';

// Descargar archivo CSV
downloadCSV($csvUrl, $localFile);

// Cargar productos desde CSV
$allProducts = loadProductsFromCSV($localFile);

// Cargar progreso anterior
$processedSKUs = loadProgress($progressFile);

// Preguntar si continuar desde el progreso anterior
echo "¿Deseas continuar desde el último producto creado/actualizado? (s/n): ";
$continue = trim(fgets(STDIN));

if (strtolower($continue) === 's') {
    $allProducts = array_filter($allProducts, function ($product) use ($processedSKUs) {
        return !in_array($product['SKU'], $processedSKUs);
    });
    echo "Continuando desde el último producto procesado...\n";
}

// Integrar productos
foreach ($allProducts as $product) {
    $sku = $product['SKU'];
    $existingProduct = getProductBySKU($woocommerce, $sku);
    $categories = processCategories($woocommerce, $product['CATEGORIES']);

    try {
        if ($existingProduct) {
            // Actualizar las categorías del producto
            $updateData = [
                'categories' => $categories,
            ];

            $woocommerce->put("products/{$existingProduct->id}", $updateData);
            echo "Producto existente '{$product['NAME']}' (SKU: $sku) actualizado con nuevas categorías.\n";
        } else {
            // Crear un nuevo producto con todas las propiedades, incluyendo las categorías
            $imageUrls = explode(',', $product['IMAGES']);
            $images = array_map(function ($url) {
                return ['src' => trim($url)];
            }, $imageUrls);

            $woocommerce->post('products', [
                'name' => $product['NAME'],
                'type' => 'simple',
                'regular_price' => '0',
                'description' => $product['DESCRIPTION'],
                'short_description' => $product['SHORT_DESCRIPTION'],
                'categories' => $categories,
                'images' => $images,
                'sku' => $sku,
                'weight' => $product['WEIGHT'] ?? '',
            ]);
            echo "Producto nuevo '{$product['NAME']}' (SKU: $sku) creado.\n";
        }
        // Guardar progreso
        saveProgress($progressFile, $sku);
    } catch (Exception $e) {
        echo "Error procesando '{$product['NAME']}' (SKU: $sku): " . $e->getMessage() . "\n";
    }
}

echo "Integración completada.\n";