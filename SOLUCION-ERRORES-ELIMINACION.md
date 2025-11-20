# 🔧 Solución a Errores de Eliminación

## Problema Reportado

```
No se eliminaron preguntas
Errores: Error ID 6072: Error escribiendo a la base de datos,
Error ID 6166: Error escribiendo a la base de datos, ...
```

## ✅ Mejoras Implementadas (Prueba primero)

Acabo de actualizar `delete.php` con las siguientes mejoras:

1. **Más tablas limpiadas** - Ahora limpia ~25 tablas relacionadas (antes solo ~10)
2. **Verificación de existencia** - Verifica que cada tabla exista antes de intentar eliminar
3. **Manejo robusto de errores** - No falla si una tabla no existe en tu versión de Moodle
4. **Limpieza de intentos de usuarios** - Elimina también los intentos previos de respuesta

### Tablas Adicionales Ahora Incluidas:

- ✅ `question_gapselect`, `question_ddwtos`, `question_ddmarker`, `question_ddimageortext`
- ✅ `qtype_*_options` (para Moodle 4.x)
- ✅ `question_attempts`, `question_attempt_steps`, `question_attempt_step_data`
- ✅ `question_references`, `question_versions`, `question_set_references`

### Cómo Actualizar

```bash
# En tu servidor Moodle
cd /var/www/html/moodle/admin/tool/questionsearch

# Opción 1: Git
git pull origin main

# Opción 2: Manual - descarga el nuevo delete.php y reemplázalo
```

Luego intenta eliminar de nuevo desde el plugin.

---

## 🔍 Si el problema persiste - Diagnóstico

Si después de actualizar sigues teniendo el error, necesitamos más información.

### Paso 1: Usar el Script de Diagnóstico

He creado un script especial para diagnosticar el problema exacto.

**URL del script:**
```
https://tu-moodle.com/admin/tool/questionsearch/delete-diagnostico.php?sesskey=TU_SESSKEY&qid=6072
```

Reemplaza:
- `tu-moodle.com` con tu dominio
- `TU_SESSKEY` con tu session key (lo ves en cualquier formulario del plugin)
- `6072` con el ID de una de las preguntas que está fallando

### Paso 2: Copiar la Información

El script mostrará:
1. ✅ Si la pregunta existe
2. 📊 Configuración de tu base de datos (prefijo, tipo)
3. 📋 Tablas que tienen referencias a esa pregunta
4. 🔗 Foreign keys (si tienes permisos para verlas)
5. 🧪 Simulación paso a paso de la eliminación

**Copia TODA esa información y envíamela.**

---

## 💡 Soluciones Alternativas (Mientras Tanto)

### Opción A: Eliminar vía SQL Directo

⚠️ **HACER BACKUP PRIMERO**

```sql
-- BACKUP
mysqldump -u usuario -p nombre_bd > backup_antes_eliminar.sql

-- Ejemplo para eliminar pregunta ID 6072
START TRANSACTION;

-- Ver qué tablas la referencian
SELECT 'quiz_slots' as tabla, COUNT(*) as refs FROM mdl_quiz_slots WHERE questionid = 6072
UNION ALL
SELECT 'question_answers', COUNT(*) FROM mdl_question_answers WHERE question = 6072
UNION ALL
SELECT 'question_attempts', COUNT(*) FROM mdl_question_attempts WHERE questionid = 6072;

-- Si estás seguro, eliminar:
DELETE FROM mdl_quiz_slots WHERE questionid = 6072;
DELETE FROM mdl_question_answers WHERE question = 6072;
DELETE FROM mdl_question_hints WHERE questionid = 6072;
DELETE FROM mdl_question_attempts WHERE questionid = 6072;
-- ... (según las tablas que mostraron referencias)

DELETE FROM mdl_question WHERE id = 6072;

-- Verificar
SELECT * FROM mdl_question WHERE id = 6072;
-- Debe dar 0 resultados

COMMIT;
-- O si algo salió mal: ROLLBACK;
```

Después de eliminar por SQL:
```bash
# Limpiar cachés
php admin/cli/purge_caches.php
```

### Opción B: Deshabilitar Foreign Key Checks Temporalmente

⚠️ **Solo si sabes lo que estás haciendo**

Puedo crear una versión especial de `delete.php` que temporalmente deshabilite las verificaciones de foreign keys:

```php
// Deshabilitar checks
$DB->execute("SET FOREIGN_KEY_CHECKS = 0");

// ... eliminar preguntas

// Re-habilitar checks
$DB->execute("SET FOREIGN_KEY_CHECKS = 1");
```

**Peligro:** Esto puede dejar la base de datos en estado inconsistente si no se maneja bien.

---

## 📋 Información Necesaria para Ayudarte Mejor

Para crear una solución perfecta para tu caso, necesito:

### 1. Versión de Moodle
```
Administración → Notificaciones → (arriba dice la versión)
Ejemplo: Moodle 3.11.15, 4.1.2, etc.
```

### 2. Prefijo de Tablas
En tu `config.php`:
```php
$CFG->prefix = 'mdl_';  // ¿Es 'mdl_' o diferente?
```

### 3. Tipo de Base de Datos
```php
$CFG->dbtype = 'mariadb';  // ¿mariadb, mysqli, pgsql?
```

### 4. Resultado del Script de Diagnóstico

Ejecuta `delete-diagnostico.php` con un ID problemático y copia TODO el resultado.

### 5. Consulta SQL de Información

Conéctate a tu BD y ejecuta:

```sql
-- Ver estructura de la tabla question
DESCRIBE mdl_question;

-- Ver foreign keys (si tienes permisos)
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME LIKE '%question%'
AND TABLE_SCHEMA = DATABASE();

-- Ver índices
SHOW INDEX FROM mdl_question;
```

---

## 🚀 Solución Personalizada

Una vez que me proporciones la información anterior, puedo crear una versión de `delete.php` específicamente optimizada para tu configuración de Moodle.

---

## 📞 Próximos Pasos

1. ✅ **Actualiza a la nueva versión de `delete.php`** (ya está en GitHub)
2. 🧪 **Intenta eliminar de nuevo** - puede que ya funcione
3. 🔍 **Si sigue fallando**, ejecuta `delete-diagnostico.php` y envíame el resultado completo
4. 📊 **Envíame la información** de versión, prefijo, tipo de BD
5. 🎯 **Crearé una solución personalizada** basada en tu configuración exacta

---

## ⚡ Actualización Rápida

```bash
cd /var/www/html/moodle/admin/tool/questionsearch
wget https://raw.githubusercontent.com/elllanerosolitario55-ai/moodlesearhc/main/delete.php -O delete.php
wget https://raw.githubusercontent.com/elllanerosolitario55-ai/moodlesearhc/main/delete-diagnostico.php -O delete-diagnostico.php
```

O descarga los archivos manualmente del repositorio.

---

**¿Necesitas ayuda urgente?** Envíame:
- Resultado de `delete-diagnostico.php`
- Versión de Moodle
- Prefijo de tablas
- Tipo de base de datos

¡Y lo resolveremos! 💪
