# Moodle Question Search & Replace Plugin

Plugin para Moodle que permite a los administradores buscar y reemplazar palabras clave en preguntas de cuestionarios de forma masiva.

## 🆕 Novedades v1.1.0 (20 Nov 2025)

- 🔒 **Sistema de transacciones** con rollback automático para garantizar integridad de datos
- ✨ **Mejor manejo de errores** con mensajes descriptivos y detallados
- 🛡️ **Validación mejorada** de IDs de preguntas antes de eliminar
- 🔧 **Eliminación de duplicados** en selecciones múltiples
- 📄 **Títulos correctos** en todas las páginas del plugin

👉 [Ver todas las novedades de v1.1.0](NOVEDADES-v1.1.0.md)

## 🎯 Características

- ✅ Búsqueda de texto en preguntas, respuestas y retroalimentación
- ✅ Filtrado por curso específico o todos los cursos
- ✅ Búsqueda sensible a mayúsculas/minúsculas (opcional)
- ✅ Coincidencia exacta o parcial
- ✅ Reemplazo masivo o selectivo
- ✅ Vista previa de resultados antes de reemplazar
- ✅ Interfaz bilingüe (Español/Inglés)
- ✅ Transacciones seguras con rollback automático en caso de error
- ✅ Limpieza de caché automática después de reemplazos

## 📋 Requisitos

- Moodle 3.9 o superior
- Permisos de administrador del sitio
- PHP 7.3 o superior

## 🚀 Instalación

### Opción 1: Instalación Manual

1. **Descarga el plugin**
   - Descarga o clona este repositorio

2. **Copia los archivos**
   ```bash
   # Navega al directorio de Moodle
   cd /ruta/a/moodle

   # Crea el directorio del plugin
   mkdir -p admin/tool/questionsearch

   # Copia todos los archivos del plugin
   cp -r /ruta/al/plugin/* admin/tool/questionsearch/
   ```

3. **Establece permisos correctos**
   ```bash
   # En el directorio de Moodle
   chown -R www-data:www-data admin/tool/questionsearch
   chmod -R 755 admin/tool/questionsearch
   ```

4. **Instala el plugin**
   - Accede a tu sitio Moodle como administrador
   - Ve a: **Administración del sitio → Notificaciones**
   - Moodle detectará el nuevo plugin y te pedirá actualizar
   - Haz clic en "Actualizar base de datos de Moodle"

### Opción 2: Instalación vía Git

```bash
cd /ruta/a/moodle/admin/tool
git clone [URL_DEL_REPOSITORIO] questionsearch
cd questionsearch
```

Luego accede a Moodle y completa la instalación desde Notificaciones.

## 📖 Uso

### 1. Acceder al plugin

Una vez instalado, accede desde:
- **Administración del sitio → Plugins → Herramientas de administración → Question Search & Replace**

O directamente:
- `https://tu-moodle.com/admin/tool/questionsearch/`

### 2. Realizar una búsqueda

1. **Término de búsqueda** (obligatorio): Ingresa la palabra o frase que deseas buscar
2. **Reemplazar con** (opcional): Si deseas reemplazar, ingresa el nuevo texto
3. **Seleccionar curso**: Elige un curso específico o busca en todos
4. **Buscar en**: Selecciona dónde buscar:
   - Texto de la pregunta
   - Retroalimentación general
   - Respuestas
5. **Opciones adicionales**:
   - ☑️ Distinguir mayúsculas/minúsculas
   - ☑️ Coincidencia exacta
6. Haz clic en **Buscar**

### 3. Ver resultados

Los resultados mostrarán:
- Curso donde se encuentra la pregunta
- Cuestionario (si está en uno)
- Nombre de la pregunta
- Tipo de pregunta
- Ubicación (texto, retroalimentación, respuesta)
- Vista previa con el término resaltado

### 4. Reemplazar texto

Si ingresaste un término de reemplazo:
1. Selecciona las preguntas que deseas modificar (o usa "Seleccionar Todo")
2. Haz clic en **Reemplazar Seleccionados**
3. Confirma la acción
4. El plugin realizará los cambios y mostrará un mensaje de confirmación

## 🔍 Ejemplos de uso

### Ejemplo 1: Cambiar un término en todo el sitio

**Escenario**: Cambiar "alumno" por "estudiante" en todas las preguntas

1. Término de búsqueda: `alumno`
2. Reemplazar con: `estudiante`
3. Seleccionar curso: `Todos los cursos`
4. Buscar en: ☑️ Todas las áreas
5. Buscar → Seleccionar Todo → Reemplazar

### Ejemplo 2: Encontrar preguntas con un error específico

**Escenario**: Buscar preguntas que mencionen una fecha incorrecta

1. Término de búsqueda: `2020`
2. Reemplazar con: `2025`
3. Seleccionar curso: `Matemáticas 101`
4. Buscar en: ☑️ Texto de la pregunta
5. Revisar resultados y reemplazar selectivamente

### Ejemplo 3: Búsqueda exacta y sensible a mayúsculas

**Escenario**: Cambiar "ONU" (organización) sin afectar "uno" (número)

1. Término de búsqueda: `ONU`
2. Reemplazar con: `Naciones Unidas`
3. Opciones: ☑️ Distinguir mayúsculas/minúsculas, ☑️ Coincidencia exacta

## 🗄️ Búsquedas directas en Base de Datos

Si prefieres trabajar directamente con SQL, aquí hay algunas consultas útiles:

### Buscar en texto de preguntas

```sql
SELECT
    q.id,
    q.name,
    q.questiontext,
    c.fullname AS curso,
    quiz.name AS cuestionario
FROM mdl_question q
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
LEFT JOIN mdl_course c ON c.id = ctx.instanceid
LEFT JOIN mdl_quiz_slots qs ON qs.questionid = q.id
LEFT JOIN mdl_quiz quiz ON quiz.id = qs.quizid
WHERE q.questiontext LIKE '%tu_termino_aqui%';
```

### Buscar en respuestas

```sql
SELECT
    qa.id,
    qa.answer,
    q.name AS pregunta,
    c.fullname AS curso
FROM mdl_question_answers qa
JOIN mdl_question q ON q.id = qa.question
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
LEFT JOIN mdl_course c ON c.id = ctx.instanceid
WHERE qa.answer LIKE '%tu_termino_aqui%';
```

### Reemplazo directo (¡USAR CON PRECAUCIÓN!)

```sql
-- SIEMPRE HACER BACKUP ANTES
-- Reemplazar en texto de preguntas
UPDATE mdl_question
SET questiontext = REPLACE(questiontext, 'texto_viejo', 'texto_nuevo'),
    timemodified = UNIX_TIMESTAMP()
WHERE questiontext LIKE '%texto_viejo%';

-- Reemplazar en respuestas
UPDATE mdl_question_answers
SET answer = REPLACE(answer, 'texto_viejo', 'texto_nuevo')
WHERE answer LIKE '%texto_viejo%';

-- Limpiar cachés (ejecutar en terminal del servidor)
php admin/cli/purge_caches.php
```

## 🔒 Seguridad

- ✅ Solo usuarios con permiso `tool/questionsearch:use` pueden acceder (administradores por defecto)
- ✅ Protección CSRF con tokens de sesión
- ✅ Uso de la API de Moodle para acceso seguro a la base de datos
- ✅ Transacciones con rollback automático en caso de error
- ✅ Limpieza automática de caché después de cambios

## ⚠️ Advertencias y Mejores Prácticas

1. **SIEMPRE haz un backup de la base de datos antes de reemplazos masivos**
   ```bash
   mysqldump -u usuario -p nombre_bd > backup_$(date +%Y%m%d).sql
   ```

2. **Prueba primero en un curso de prueba**

3. **Revisa cuidadosamente los resultados antes de reemplazar**

4. **Ten en cuenta que HTML puede afectar la búsqueda**
   - Ejemplo: `<p>texto</p>` es diferente a `texto`

5. **Los cambios son permanentes** - No hay función de "deshacer"

6. **Después de reemplazos masivos, verifica algunas preguntas manualmente**

## 🛠️ Solución de Problemas

### El plugin no aparece en el menú

1. Verifica que los archivos estén en `moodle/admin/tool/questionsearch/`
2. Ve a Notificaciones para completar la instalación
3. Verifica que tengas permisos de administrador

### No encuentra resultados esperados

1. Prueba sin "Coincidencia exacta"
2. Desactiva "Distinguir mayúsculas/minúsculas"
3. Recuerda que el HTML puede interferir - usa "Inspeccionar elemento" en el navegador para ver el HTML real

### Error al reemplazar

1. Verifica permisos de escritura en la base de datos
2. Revisa los logs de Moodle: `Administración → Informes → Registros`
3. Verifica que no haya preguntas bloqueadas o en uso

### Cambios no se reflejan inmediatamente

1. El plugin limpia cachés automáticamente
2. Si persiste, limpia cachés manualmente: `Administración → Desarrollo → Purgar todas las cachés`
3. O por CLI: `php admin/cli/purge_caches.php`

## 📊 Tablas de Moodle afectadas

Este plugin trabaja con las siguientes tablas:

- `mdl_question` - Preguntas (texto y retroalimentación)
- `mdl_question_answers` - Respuestas de preguntas
- `mdl_question_versions` - Versiones de preguntas
- `mdl_question_bank_entries` - Entradas del banco de preguntas
- `mdl_quiz` - Cuestionarios
- `mdl_quiz_slots` - Relación pregunta-cuestionario
- `mdl_course` - Cursos

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Haz un fork del repositorio
2. Crea una rama para tu feature
3. Haz commit de tus cambios
4. Push a la rama
5. Crea un Pull Request

## 📝 Licencia

Este plugin es software libre: puedes redistribuirlo y/o modificarlo bajo los términos de la GNU General Public License publicada por la Free Software Foundation, versión 3 o posterior.

## 👨‍💻 Autor

Desarrollado para facilitar la administración de contenido en Moodle.

## 📞 Soporte

Si encuentras bugs o tienes sugerencias:
- Abre un issue en el repositorio
- Contacta al administrador de tu plataforma Moodle

---

**Versión**: 1.1.0
**Última actualización**: 20 Noviembre 2025
**Compatible con**: Moodle 3.9+
**GitHub**: https://github.com/elllanerosolitario55-ai/moodlesearhc
