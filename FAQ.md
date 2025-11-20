# Preguntas Frecuentes (FAQ)

## General

### ¿Qué hace este plugin exactamente?

El plugin permite buscar y reemplazar texto en preguntas de cuestionarios de Moodle de forma masiva. Puedes:
- Buscar palabras o frases en textos de preguntas, retroalimentación y respuestas
- Filtrar por curso específico o buscar en todos los cursos
- Reemplazar el texto encontrado de forma selectiva o masiva
- Ver vista previa antes de aplicar cambios

### ¿Es seguro usar este plugin?

Sí, el plugin incluye múltiples medidas de seguridad:
- Usa transacciones de base de datos con rollback automático en caso de error
- Requiere permisos de administrador
- Protección CSRF
- Limpieza automática de caché después de cambios
- **Sin embargo**, siempre se recomienda hacer backup antes de reemplazos masivos

### ¿Funciona con todas las versiones de Moodle?

El plugin es compatible con Moodle 3.9 y versiones superiores. Ha sido probado en:
- Moodle 3.9, 3.10, 3.11
- Moodle 4.0, 4.1, 4.2

## Instalación

### ¿Dónde debo instalar los archivos?

Los archivos deben ir en: `/ruta/a/moodle/admin/tool/questionsearch/`

### No aparece en el menú después de instalar

1. Asegúrate de haber ido a "Notificaciones" para completar la instalación
2. Verifica que los archivos estén en la ruta correcta
3. Limpia las cachés: Administración → Desarrollo → Purgar cachés
4. Cierra sesión y vuelve a entrar
5. Verifica que tengas rol de administrador

### Error de permisos al instalar

Ejecuta en el servidor:
```bash
sudo chown -R www-data:www-data /var/www/html/moodle/admin/tool/questionsearch
sudo chmod -R 755 /var/www/html/moodle/admin/tool/questionsearch
```

Ajusta `www-data` según tu configuración (puede ser `apache`, `nginx`, etc.)

## Uso

### ¿Cómo busco texto que contiene HTML?

Moodle almacena contenido con HTML. Si buscas "texto" y no encuentras nada, puede estar almacenado como `<p>texto</p>`.

Soluciones:
- Usa el navegador para inspeccionar el elemento y ver el HTML real
- Busca solo parte del texto sin etiquetas
- Usa búsqueda directa por SQL (ver `sql_examples.sql`)

### ¿La búsqueda es sensible a mayúsculas?

Por defecto NO, pero puedes activar esta opción marcando la casilla "Distinguir mayúsculas/minúsculas" en el formulario de búsqueda.

### No encuentra resultados que sé que existen

Posibles causas:
1. **HTML**: El texto puede tener HTML embebido
2. **Coincidencia exacta**: Desactiva esta opción si está marcada
3. **Mayúsculas**: Desactiva "sensible a mayúsculas" si está marcada
4. **Espacios**: Puede haber espacios extra o caracteres especiales
5. **Curso incorrecto**: Verifica que el filtro de curso sea correcto

### ¿Puedo deshacer un reemplazo?

**No directamente**. Por eso es crucial:
1. Hacer backup ANTES de reemplazar
2. Revisar cuidadosamente los resultados antes de reemplazar
3. Probar primero en un curso de prueba

Si necesitas restaurar:
```bash
mysql -u usuario -p nombre_bd < backup.sql
```

### ¿Los cambios se ven inmediatamente?

Sí, el plugin limpia automáticamente las cachés. Si no ves los cambios:
1. Ve a Administración → Desarrollo → Purgar todas las cachés
2. Cierra sesión y vuelve a entrar
3. Limpia la caché de tu navegador (Ctrl+F5)

### ¿Puedo buscar en actividades que no sean cuestionarios?

Esta versión (1.0.0) solo busca en preguntas de cuestionarios. Futuras versiones podrían incluir:
- Páginas
- Recursos
- Etiquetas
- Tareas
- Otros módulos

## Rendimiento

### ¿Es lento con muchos cursos?

La búsqueda puede tardar más con:
- Miles de preguntas
- Búsqueda en todos los cursos
- Búsqueda en todas las áreas (texto + feedback + respuestas)

**Recomendaciones:**
- Filtra por curso específico cuando sea posible
- Busca solo en áreas necesarias
- En bases de datos muy grandes, considera usar SQL directo (ver `sql_examples.sql`)

### ¿Afecta el rendimiento de Moodle?

No. El plugin solo se ejecuta cuando lo usas activamente. No afecta el rendimiento general del sitio.

## Problemas Comunes

### Error: "No tiene permisos para usar esta herramienta"

Solo usuarios con rol de administrador (o con el permiso `tool/questionsearch:use`) pueden usar el plugin.

Verifica tus permisos en: Administración → Usuarios → Permisos → Definir roles

### Los cambios no se guardaron

Posibles causas:
1. **Error de base de datos**: Revisa logs de Moodle
2. **Timeout**: Búsqueda muy grande, aumenta el timeout de PHP
3. **Permisos**: Verifica permisos de escritura en BD
4. **Transacción falló**: Revisa logs para ver el error exacto

### Error 500 al buscar

1. Verifica logs de PHP: `/var/log/apache2/error.log` o `/var/log/nginx/error.log`
2. Puede ser timeout de PHP, aumenta en `php.ini`:
   ```
   max_execution_time = 300
   memory_limit = 256M
   ```
3. Reinicia el servidor web después de cambios en `php.ini`

### El formulario no se envía

1. Verifica que JavaScript esté habilitado en tu navegador
2. Revisa la consola del navegador (F12) para errores
3. Asegúrate de haber ingresado al menos el término de búsqueda

## Base de Datos

### ¿Puedo usar SQL directamente en lugar del plugin?

Sí, ver archivo `sql_examples.sql` con ejemplos.

**IMPORTANTE:**
- SIEMPRE haz backup antes
- Prueba primero con SELECT
- Usa transacciones
- Limpia cachés después

### ¿Qué tablas de Moodle usa el plugin?

Principales:
- `mdl_question` - Preguntas
- `mdl_question_answers` - Respuestas
- `mdl_question_versions` - Versiones
- `mdl_question_bank_entries` - Banco de preguntas
- `mdl_quiz` - Cuestionarios
- `mdl_quiz_slots` - Relación pregunta-cuestionario
- `mdl_course` - Cursos

### ¿Cuál es el prefijo de tablas en mi Moodle?

Por defecto es `mdl_`, pero puede ser diferente.

Para verificar:
1. Ve a `config.php` en la raíz de Moodle
2. Busca: `$CFG->prefix = 'mdl_';`
3. Usa ese prefijo en consultas SQL

## Seguridad

### ¿Quién puede usar el plugin?

Solo usuarios con:
- Rol de administrador (por defecto)
- O usuarios con el permiso `tool/questionsearch:use` asignado específicamente

### ¿Se registran los cambios?

Los cambios se reflejan en:
- Campo `timemodified` de las preguntas
- Logs estándar de Moodle (si tienes logging habilitado)

Para auditoría más detallada, considera:
- Hacer backup antes de cambios importantes
- Revisar logs de Moodle: Administración → Informes → Registros

### ¿Puede un profesor usar este plugin?

No por defecto. Solo administradores tienen acceso.

Si quieres dar acceso a profesores específicos:
1. Ve a: Administración → Usuarios → Permisos → Definir roles
2. Edita el rol de profesor
3. Busca `tool/questionsearch:use` y márcalo como "Permitir"

**Advertencia:** Esto les dará acceso a modificar preguntas en todos los cursos.

## Backup y Recuperación

### ¿Cómo hago backup antes de usar el plugin?

**Opción 1: Script incluido**
```bash
chmod +x backup_script.sh
./backup_script.sh
```

**Opción 2: Manual**
```bash
mysqldump -u root -p moodle > backup_$(date +%Y%m%d).sql
```

**Opción 3: Desde Moodle**
- Administración → Cursos → Copias de seguridad → Copia de seguridad automática

### ¿Cómo restauro desde un backup?

```bash
# Descomprimir si está comprimido
gunzip backup_20251117.sql.gz

# Restaurar
mysql -u root -p moodle < backup_20251117.sql

# Limpiar cachés
cd /var/www/html/moodle
php admin/cli/purge_caches.php
```

### ¿Cuánto espacio ocupan los backups?

Depende del tamaño de tu base de datos. Ejemplos:
- Moodle pequeño (< 1000 preguntas): 10-50 MB
- Moodle mediano (1000-10000 preguntas): 50-500 MB
- Moodle grande (> 10000 preguntas): 500 MB - varios GB

Los backups comprimidos (.gz) son ~10x más pequeños.

## Soporte y Desarrollo

### ¿Dónde reporto bugs?

Abre un issue en el repositorio del proyecto o contacta al administrador de tu instalación de Moodle.

### ¿Puedo contribuir al desarrollo?

¡Sí! Las contribuciones son bienvenidas:
1. Fork del repositorio
2. Crea una rama para tu feature
3. Commit y push
4. Abre un Pull Request

### ¿Habrá actualizaciones?

Ver `CHANGELOG.md` para el roadmap de futuras versiones planificadas.

### ¿Es gratis?

Sí, este plugin es software libre bajo licencia GPL v3. Puedes usarlo, modificarlo y redistribuirlo libremente.

## Más Ayuda

### No encuentro mi pregunta aquí

1. Revisa la documentación completa en `README.md`
2. Revisa los logs de Moodle: Administración → Informes → Registros
3. Revisa los logs del servidor: `/var/log/apache2/error.log`
4. Contacta al administrador de tu plataforma Moodle
5. Abre un issue en el repositorio del proyecto

### ¿Hay video tutorial?

Actualmente no, pero la interfaz es bastante intuitiva. Si tienes interés en crear uno, ¡las contribuciones son bienvenidas!

### ¿Funciona en Moodle Cloud?

Si usas Moodle Cloud (servicio oficial de Moodle), probablemente no tengas acceso para instalar plugins personalizados. Contacta a Moodle para confirmar.

Si tienes tu propia instalación de Moodle, sí funciona sin problemas.
