<?php
/**
 * Archivo de configuración de ejemplo para backup_script.sh
 *
 * Este archivo NO es necesario para el funcionamiento del plugin.
 * Es solo una referencia para configurar backups automáticos.
 *
 * Para usar el script de backup:
 * 1. Edita backup_script.sh con tus credenciales
 * 2. O crea un archivo .my.cnf en tu home con las credenciales
 */

// Ejemplo de configuración de conexión a MySQL
// (para referencia, NO usar en producción con credenciales reales)

/*
[client]
user=root
password=tu_password_aqui
host=localhost

[mysqldump]
user=root
password=tu_password_aqui
host=localhost
*/

/**
 * Configuración alternativa usando ~/.my.cnf
 *
 * Crea el archivo en tu home:
 * nano ~/.my.cnf
 *
 * Contenido:
 * [client]
 * user=moodle_user
 * password=tu_password_seguro
 * host=localhost
 *
 * [mysqldump]
 * user=moodle_user
 * password=tu_password_seguro
 * host=localhost
 *
 * Protege el archivo:
 * chmod 600 ~/.my.cnf
 *
 * Luego puedes ejecutar mysqldump sin -p:
 * mysqldump moodle > backup.sql
 */

/**
 * Ejemplo de cron job para backups automáticos
 *
 * Edita crontab:
 * crontab -e
 *
 * Agregar línea para backup diario a las 2 AM:
 * 0 2 * * * /ruta/a/moodle/admin/tool/questionsearch/backup_script.sh >> /var/log/moodle_backup.log 2>&1
 *
 * O semanal (domingos a las 3 AM):
 * 0 3 * * 0 /ruta/a/moodle/admin/tool/questionsearch/backup_script.sh >> /var/log/moodle_backup.log 2>&1
 */

/**
 * Variables de entorno recomendadas para PHP
 *
 * En php.ini o .htaccess:
 *
 * max_execution_time = 300
 * memory_limit = 256M
 * post_max_size = 20M
 * upload_max_filesize = 20M
 */

/**
 * Configuración de Moodle relevante
 *
 * En config.php de Moodle puedes agregar:
 */

// Habilitar debugging durante desarrollo del plugin
// $CFG->debug = (E_ALL | E_STRICT);
// $CFG->debugdisplay = 1;

// Deshabilitar caché durante desarrollo
// $CFG->cachejs = false;
// $CFG->cachetemplates = false;

// En producción, SIEMPRE usar:
// $CFG->debug = 0;
// $CFG->debugdisplay = 0;

/**
 * Prefijo de tablas de Moodle
 *
 * Por defecto es 'mdl_' pero puede variar.
 * Ver en config.php:
 * $CFG->prefix = 'mdl_';
 *
 * Si tu instalación usa otro prefijo (ej: 'm_', 'moodle_', etc.),
 * actualiza las consultas SQL en sql_examples.sql
 */

/**
 * Rutas comunes de Moodle
 */

// Directorio raíz de Moodle
// /var/www/html/moodle
// /usr/share/moodle
// /home/usuario/public_html/moodle

// Directorio de datos (moodledata)
// /var/moodledata
// /var/www/moodledata
// /home/usuario/moodledata

// Logs de Apache/Nginx
// /var/log/apache2/error.log
// /var/log/nginx/error.log

// Logs de PHP
// /var/log/php/error.log
// /var/log/php-fpm/error.log

/**
 * Comandos útiles de CLI de Moodle
 */

// Limpiar cachés
// php admin/cli/purge_caches.php

// Actualizar base de datos
// php admin/cli/upgrade.php

// Mantenimiento ON
// php admin/cli/maintenance.php --enable

// Mantenimiento OFF
// php admin/cli/maintenance.php --disable

// Instalar plugin desde CLI
// php admin/cli/upgrade.php --non-interactive

/**
 * Permisos recomendados de archivos
 */

// Archivos: 644
// Directorios: 755
// config.php: 640 (más restrictivo)

// Comando para aplicar permisos:
// find /var/www/html/moodle -type f -exec chmod 644 {} \;
// find /var/www/html/moodle -type d -exec chmod 755 {} \;
// chmod 640 /var/www/html/moodle/config.php

/**
 * Propiedad de archivos (ajustar según tu configuración)
 */

// chown -R www-data:www-data /var/www/html/moodle
// o
// chown -R apache:apache /var/www/html/moodle
// o
// chown -R nginx:nginx /var/www/html/moodle
