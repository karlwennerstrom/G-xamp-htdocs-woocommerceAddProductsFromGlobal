
# WooCommerce Product Integration Script

Este proyecto es un script en PHP que automatiza la integración de productos desde un archivo CSV en una tienda WooCommerce utilizando la API REST de WooCommerce. Permite descargar productos desde una fuente externa, procesar sus datos y actualizarlos o crearlos en tu tienda, gestionando categorías e imágenes.

## Características

- **Descarga automática del CSV:** Obtiene el archivo desde una URL configurada.
- **Procesamiento de productos:** Verifica la existencia de productos por SKU y actualiza o crea nuevos.
- **Gestión de categorías:** Crea categorías automáticamente si no existen.
- **Manejo de progreso:** Permite reanudar el procesamiento desde donde se quedó utilizando un archivo de registro.
- **Soporte de imágenes:** Asigna múltiples imágenes a los productos desde URLs.

## Requisitos

- **Servidor:**
  - PHP 7.4 o superior.
  - Extensión `curl` habilitada.
  - Composer instalado.

- **WooCommerce:**
  - WooCommerce configurado con la API REST habilitada.
  - Claves de API REST (Consumer Key y Consumer Secret).

## Instalación

1. **Clonar el repositorio:**

   ```bash
   git clone https://github.com/karlwennerstrom/G-xamp-htdocs-woocommerceAddProductsFromGlobal.git
   cd G-xamp-htdocs-woocommerceAddProductsFromGlobal
   ```

2. **Instalar dependencias:**

   Usa Composer para instalar las dependencias requeridas:

   ```bash
   composer install
   ```

3. **Configurar las credenciales de WooCommerce:**

   Edita el archivo PHP principal y actualiza las credenciales de WooCommerce:

   ```php
   $woocommerce = new Client(
       'https://tu-tienda.com', // URL de tu tienda WooCommerce
       'ck_tu_consumer_key',    // Consumer Key
       'cs_tu_consumer_secret', // Consumer Secret
       ['version' => 'wc/v3']   // Versión de la API
   );
   ```

4. **Configurar la URL del CSV:**

   Asegúrate de que la URL del archivo CSV sea válida y esté configurada en el script:

   ```php
   $csvUrl = 'https://example.com/path-to-your-csv-file.csv';
   ```

## Uso

1. **Ejecutar el script:**

   Corre el script desde la línea de comandos:

   ```bash
   php script.php
   ```

2. **Opciones de progreso:**
   Si has ejecutado el script antes, se te preguntará si deseas continuar desde donde se quedó:

   ```bash
   ¿Deseas continuar desde el último producto creado/actualizado? (s/n):
   ```

3. **Salida esperada:**
   El script generará mensajes indicando si un producto fue actualizado o creado. Ejemplo:

   ```plaintext
   Producto existente 'Producto A' (SKU: ABC123) actualizado con nuevas categorías.
   Producto nuevo 'Producto B' (SKU: DEF456) creado.
   Integración completada.
   ```

## Estructura del CSV

El archivo CSV debe contener las siguientes columnas:

| Campo              | Descripción                                     | Obligatorio |
|---------------------|-------------------------------------------------|-------------|
| `SKU`              | Código único del producto                       | Sí          |
| `NAME`             | Nombre del producto                             | Sí          |
| `DESCRIPTION`      | Descripción larga del producto                  | Sí          |
| `SHORT_DESCRIPTION`| Descripción breve del producto                  | No          |
| `CATEGORIES`       | Categorías separadas por comas o punto y coma   | No          |
| `IMAGES`           | URLs de imágenes separadas por comas            | No          |
| `WEIGHT`           | Peso del producto                               | No          |

## Seguridad

- **Protege tus claves de WooCommerce:** No compartas públicamente tus claves API (`Consumer Key` y `Consumer Secret`).
- **Acceso al archivo CSV:** Asegúrate de que la URL del archivo CSV esté segura y accesible solo para usuarios autorizados.

## Personalización

Si necesitas modificar el script:
- Ajusta el formato de las columnas esperadas en la función `loadProductsFromCSV`.
- Personaliza los datos enviados a WooCommerce en la sección de creación de productos.

## Licencia

Este proyecto está distribuido bajo la licencia MIT. Puedes consultar más detalles en el archivo `LICENSE`.

---

**Autor:**  
Karl Wennerstrom  
Si tienes dudas o necesitas soporte, no dudes en contactarme a través de este repositorio.
