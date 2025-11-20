# Guía Rápida de Instalación

## Método 1: Instalación Manual (Recomendado)

### Paso 1: Preparar archivos

```bash
# Conecta por SSH a tu servidor Moodle
ssh usuario@tu-servidor.com

# Navega al directorio de Moodle
cd /var/www/html/moodle
# o donde esté tu instalación de Moodle

# Crea el directorio del plugin
sudo mkdir -p admin/tool/questionsearch
```

### Paso 2: Copiar archivos

**Opción A: Subir por SFTP/FTP**
- Sube todos los archivos del plugin a: `/moodle/admin/tool/questionsearch/`

**Opción B: Copiar desde servidor**
```bash
# Si los archivos están en /tmp/plugin
sudo cp -r /tmp/moodle-search-replace-plugin/* /var/www/html/moodle/admin/tool/questionsearch/
```

**Opción C: Clonar desde Git**
```bash
cd /var/www/html/moodle/admin/tool/
sudo git clone [URL_DEL_REPO] questionsearch
```

### Paso 3: Establecer permisos

```bash
# Ajusta según tu configuración (usuario web puede ser www-data, apache, nginx, etc.)
sudo chown -R www-data:www-data /var/www/html/moodle/admin/tool/questionsearch
sudo chmod -R 755 /var/www/html/moodle/admin/tool/questionsearch
```

### Paso 4: Instalar desde Moodle

1. Accede a tu Moodle como **administrador**
2. Ve a: **Administración del sitio** → **Notificaciones**
3. Verás "Se han detectado plugins nuevos"
4. Haz clic en **"Actualizar base de datos de Moodle"**
5. Revisa la información del plugin y haz clic en **"Continuar"**
6. ✅ ¡Instalación completa!

## Método 2: Instalación vía interfaz web

1. Comprime el plugin en formato ZIP:
   ```bash
   zip -r questionsearch.zip moodle-search-replace-plugin/
   ```

2. En Moodle, ve a:
   - **Administración del sitio** → **Plugins** → **Instalar plugins**

3. Arrastra el archivo ZIP o haz clic en "Seleccionar un archivo"

4. Sigue el asistente de instalación

## Verificar instalación

1. Ve a: **Administración del sitio** → **Plugins** → **Resumen de plugins**
2. Busca "Question Search & Replace" en "Herramientas de administración"
3. Debe aparecer con estado "Estándar" y versión 1.0.0

## Acceder al plugin

**Ruta 1:**
- Administración del sitio → Plugins → Herramientas de administración → Question Search & Replace

**Ruta 2:**
- URL directa: `https://tu-moodle.com/admin/tool/questionsearch/`

## Estructura de archivos (verificación)

Tu directorio debe verse así:

```
/var/www/html/moodle/admin/tool/questionsearch/
├── version.php
├── settings.php
├── index.php
├── search.php
├── replace.php
├── README.md
├── INSTALL.md
├── db/
│   └── access.php
└── lang/
    ├── en/
    │   └── tool_questionsearch.php
    └── es/
        └── tool_questionsearch.php
```

## Solución de problemas comunes

### Error: "No se puede escribir en el directorio"
```bash
sudo chown -R www-data:www-data /var/www/html/moodle/admin/tool/questionsearch
sudo chmod -R 755 /var/www/html/moodle/admin/tool/questionsearch
```

### Error: "Plugin no se detecta"
1. Verifica que los archivos estén en la ruta correcta
2. Asegúrate de que `version.php` existe y es legible
3. Limpia la caché: Administración → Desarrollo → Purgar cachés

### No aparece en el menú
1. Ve a Notificaciones y completa la instalación
2. Verifica que tengas rol de administrador
3. Cierra sesión y vuelve a entrar

## Backup antes de usar

**Recomendación:** Antes de usar el plugin para reemplazos, haz backup:

```bash
# Backup de base de datos
mysqldump -u root -p moodle_db > backup_moodle_$(date +%Y%m%d_%H%M%S).sql

# Backup de archivos (opcional)
tar -czf moodle_files_backup_$(date +%Y%m%d).tar.gz /var/www/html/moodle/
```

## Desinstalación (si es necesario)

1. **Desde interfaz Moodle:**
   - Administración → Plugins → Resumen de plugins
   - Busca "Question Search & Replace"
   - Click en "Desinstalar"

2. **Manual:**
   ```bash
   sudo rm -rf /var/www/html/moodle/admin/tool/questionsearch
   ```
   Luego ve a Notificaciones en Moodle

## ¿Necesitas ayuda?

- Revisa el README.md completo
- Verifica logs de Moodle: Administración → Informes → Registros
- Revisa logs del servidor: `/var/log/apache2/error.log` o `/var/log/nginx/error.log`
