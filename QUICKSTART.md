# 🚀 Guía Rápida - Quick Start

## ⚡ Instalación en 3 Pasos

### 1️⃣ Copiar archivos
```bash
# SSH a tu servidor
cd /var/www/html/moodle  # o tu ruta de Moodle
sudo mkdir -p admin/tool/questionsearch
sudo cp -r /ruta/a/plugin/* admin/tool/questionsearch/
sudo chown -R www-data:www-data admin/tool/questionsearch
```

### 2️⃣ Instalar en Moodle
- Ve a tu Moodle como admin
- **Administración del sitio** → **Notificaciones**
- Click en **"Actualizar base de datos"**
- ✅ ¡Listo!

### 3️⃣ Acceder
- **Administración del sitio** → **Plugins** → **Herramientas de administración** → **Question Search & Replace**
- O directamente: `https://tu-moodle.com/admin/tool/questionsearch/`

---

## 🔍 Uso Básico

### Buscar y Reemplazar en 4 Clics

1. **Ingresa el término a buscar** (ej: "2020")
2. **Ingresa el reemplazo** (ej: "2025") - opcional
3. **Selecciona curso** o deja "Todos los cursos"
4. **Click en Buscar** 🔎

### Ejemplo Práctico

**Escenario:** Cambiar "profesor" por "docente" en todas las preguntas del curso "Matemáticas 101"

```
┌─────────────────────────────────────────┐
│  Término de búsqueda:  profesor        │
│  Reemplazar con:       docente         │
│  Seleccionar curso:    Matemáticas 101 │
│  ☑ Texto de la pregunta               │
│  ☑ Retroalimentación general          │
│  ☑ Respuestas                         │
│                                        │
│            [ Buscar ]                  │
└─────────────────────────────────────────┘
```

**Resultados:**
- Ve la lista de preguntas con "profesor"
- Selecciona las que quieres cambiar
- Click en **"Reemplazar Seleccionados"**
- ✅ ¡Listo! Los cambios se aplican instantáneamente

---

## ⚠️ Antes de Empezar

### ✅ Checklist Pre-Reemplazo

```bash
# 1. HACER BACKUP (¡IMPORTANTE!)
mysqldump -u root -p moodle > backup_$(date +%Y%m%d).sql

# 2. O usar el script incluido
chmod +x backup_script.sh
./backup_script.sh

# 3. Verificar permisos
# Debes ser administrador de Moodle
```

### 🛡️ Mejores Prácticas

- ✅ **SIEMPRE** haz backup antes de reemplazos masivos
- ✅ Prueba primero con 1-2 preguntas
- ✅ Revisa los resultados antes de reemplazar todo
- ✅ Verifica manualmente algunas preguntas después
- ❌ No uses en producción sin probar primero

---

## 📚 Casos de Uso Comunes

### 1. Corregir un Error Recurrente
```
Buscar:    "31 de Febrero"
Reemplazar: "28 de Febrero"
Curso:     Todos
→ Encuentra y corrige el error en todas las preguntas
```

### 2. Actualizar Año Académico
```
Buscar:    "2024"
Reemplazar: "2025"
Curso:     Todos
Opciones:  ☑ Coincidencia exacta (para no cambiar "2024-2025")
```

### 3. Cambiar Terminología
```
Buscar:    "alumno"
Reemplazar: "estudiante"
Curso:     Específico
→ Unifica terminología en un curso
```

### 4. Solo Buscar (Sin Reemplazar)
```
Buscar:    "COVID-19"
Reemplazar: [dejar vacío]
→ Encuentra todas las menciones para revisar manualmente
```

---

## 🔧 Solución Rápida de Problemas

### ❌ No aparece en el menú
```bash
# Limpia cachés
php admin/cli/purge_caches.php
# O desde Moodle: Administración → Desarrollo → Purgar cachés
```

### ❌ Error de permisos
```bash
sudo chown -R www-data:www-data admin/tool/questionsearch
sudo chmod -R 755 admin/tool/questionsearch
```

### ❌ No encuentra resultados esperados
- Desactiva "Distinguir mayúsculas/minúsculas"
- Desactiva "Coincidencia exacta"
- Busca solo una palabra en lugar de frase completa
- Recuerda: HTML puede interferir (`<p>texto</p>` vs `texto`)

### ❌ Error al reemplazar
1. Verifica que hiciste backup
2. Revisa logs: Administración → Informes → Registros
3. Si persiste, usa SQL directo (ver `sql_examples.sql`)

---

## 📊 Interfaz del Plugin

### Formulario de Búsqueda
```
┌─────────────────────────────────────────────────────┐
│  BUSCAR Y REEMPLAZAR EN PREGUNTAS                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Término de búsqueda: *                            │
│  ┌───────────────────────────────────────────────┐ │
│  │ texto a buscar                                │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  Reemplazar con:                                   │
│  ┌───────────────────────────────────────────────┐ │
│  │ texto nuevo                                   │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  Seleccionar curso:                                │
│  ┌───────────────────────────────────────────────┐ │
│  │ Todos los cursos ▼                            │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  Buscar en:                                        │
│  ☑ Texto de la pregunta                           │
│  ☑ Retroalimentación general                      │
│  ☑ Respuestas                                     │
│                                                     │
│  ☐ Distinguir mayúsculas/minúsculas               │
│  ☐ Coincidencia exacta                            │
│                                                     │
│                  [ Buscar ]                        │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Resultados
```
┌─────────────────────────────────────────────────────┐
│  RESULTADOS DE BÚSQUEDA                             │
│  Se encontraron 15 resultados                       │
├─────────────────────────────────────────────────────┤
│  ☐ | Curso | Quiz | Pregunta | Tipo | Ubicación  │
│  ☐ | Mat101 | Exam1 | Q1 | multichoice | texto   │
│  ☐ | Mat101 | Exam1 | Q2 | truefalse | respuesta │
│  ...                                                │
│                                                     │
│  [ Seleccionar Todo ] [ Deseleccionar ]            │
│  [ Reemplazar Seleccionados ]                      │
└─────────────────────────────────────────────────────┘
```

---

## 🗄️ Alternativa: SQL Directo

Si prefieres usar SQL directamente:

```sql
-- 1. VER QUÉ SE VA A CAMBIAR
SELECT id, name, questiontext
FROM mdl_question
WHERE questiontext LIKE '%texto_viejo%';

-- 2. HACER BACKUP
-- mysqldump -u root -p moodle > backup.sql

-- 3. REEMPLAZAR
START TRANSACTION;

UPDATE mdl_question
SET questiontext = REPLACE(questiontext, 'texto_viejo', 'texto_nuevo'),
    timemodified = UNIX_TIMESTAMP()
WHERE questiontext LIKE '%texto_viejo%';

COMMIT;

-- 4. LIMPIAR CACHÉS
-- php admin/cli/purge_caches.php
```

📄 Más ejemplos en: `sql_examples.sql`

---

## 📖 Documentación Completa

| Archivo | Descripción |
|---------|-------------|
| **README.md** | Documentación completa y detallada |
| **INSTALL.md** | Guía paso a paso de instalación |
| **FAQ.md** | Preguntas frecuentes y soluciones |
| **ESTRUCTURA.md** | Estructura técnica del plugin |
| **sql_examples.sql** | Consultas SQL de ejemplo |
| **CHANGELOG.md** | Historial de versiones |

---

## 🆘 Ayuda Rápida

### ¿Necesitas restaurar un backup?
```bash
gunzip backup_20251117.sql.gz  # si está comprimido
mysql -u root -p moodle < backup_20251117.sql
php admin/cli/purge_caches.php
```

### ¿Quieres automatizar backups?
```bash
# Agregar a crontab (diario a las 2 AM)
crontab -e
0 2 * * * /path/to/backup_script.sh >> /var/log/moodle_backup.log 2>&1
```

### ¿Problema no resuelto?
1. 📖 Lee el FAQ.md
2. 📝 Revisa logs de Moodle: Administración → Informes → Registros
3. 🐛 Revisa logs del servidor: `/var/log/apache2/error.log`
4. 💬 Contacta a tu administrador de Moodle
5. 🐙 Abre un issue en el repositorio

---

## ✨ ¡Eso es Todo!

Con esto ya puedes:
- ✅ Buscar texto en preguntas
- ✅ Reemplazar de forma masiva
- ✅ Filtrar por curso
- ✅ Hacer backups
- ✅ Resolver problemas comunes

**🎯 Tip Pro:** Siempre prueba primero en un curso de test antes de aplicar cambios masivos en producción.

---

**Versión:** 1.0.0
**Actualizado:** Noviembre 2025
**Soporte:** Ver README.md

¡Feliz búsqueda y reemplazo! 🎉
